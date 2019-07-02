<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Route;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Route|null find($id, $lockMode = null, $lockVersion = null)
 * @method Route|null findOneBy(array $criteria, array $orderBy = null)
 * @method Route[]    findAll()
 * @method Route[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RouteRepository extends ServiceEntityRepository
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $preloadedRoutes;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Route::class);
    }

    public function preloadRoutes(DateTime $startTime, DateTime $finishTime, int $maxPrice, ?int $routeMaxAge = null)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.departureDay >= :startTime');
        $qb->andWhere('r.departureDay <= :finishTime');
        $qb->andWhere('r.price <= :maxPrice');
        $qb->orderBy('r.departureDay');
        $qb->orderBy('r.price');

        $qb->setParameter('startTime', $startTime);
        $qb->setParameter('finishTime', $finishTime);
        $qb->setParameter('maxPrice', $maxPrice);

        $this->addAgeCondition($qb, $routeMaxAge);

        $queryResult = $qb->getQuery()->getResult();

        $this->preloadedRoutes = [];
        foreach($queryResult as $route)
        {
            $origin = $route->getOrigin()->getCode();
            $departureDay = $route->getDepartureDay() ? $route->getDepartureDay()->format(self::DATETIME_FORMAT) : null;
            $this->preloadedRoutes[$origin][$departureDay][] = $route;
        }
    }

    /**
     * @param City     $startCity
     * @param DateTime $startTime
     * @param DateTime $finishTime
     * @param int      $maxPrice
     *
     * @return Route[]
     */
    public function getRoutesFromCity(City $startCity, DateTime $startTime, DateTime $finishTime, int $maxPrice)
    {
        if(is_null($this->preloadedRoutes))
        {
            return $this->getRoutesFromCityFromDB($startCity, $startTime, $finishTime, $maxPrice);
        }
        else
        {
            return $this->getRoutesFromCityFromPreloadedCache($startCity, $startTime, $finishTime, $maxPrice);
        }
    }

    public function getMinMaxPricesForStatMap(): array
    {
        $query = "
            SELECT MAX(price), MIN(price), COUNT(*) FROM ({$this->getQueryForStatMap()})t
        ";

        $result = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
        return array_values(reset($result));
    }

    public function getPricesForStatMap(): array
    {
        $query = $this->getQueryForStatMap();

        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }

    /**
     * @param City $startCity
     * @param City $finishCity
     * @param DateTime $startTime
     * @param DateTime $finishTime
     * @return Route|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCheapestDirectRoute(
        City $startCity,
        City $finishCity,
        DateTime $startTime,
        DateTime $finishTime,
        ?int $routeMaxAge = null
    ): ?Route
    {
        $qb = $this->createQueryBuilder('r');

        $qb->where('r.origin = :origin');
        $qb->andWhere('r.destination = :destination');
        $qb->andWhere('r.departureDay >= :startTime');
        $qb->andWhere('r.departureDay <= :finishTime');
        $qb->orderBy('r.price', 'ASC');

        $qb->setParameter('origin', $startCity);
        $qb->setParameter('destination', $finishCity);
        $qb->setParameter('startTime', $startTime);
        $qb->setParameter('finishTime', $finishTime);

        $this->addAgeCondition($qb, $routeMaxAge);

        $qb->setMaxResults(1);

        return $qb->getQuery()->useResultCache(true)->getOneOrNullResult();
    }

    /**
     * @param City $startCity
     * @param DateTime $startTime
     * @param DateTime $finishTime
     * @param int $maxPrice
     * @param int|null $routeMaxAge
     * @return array
     * @throws \Exception
     */
    private function getRoutesFromCityFromDB(
        City $startCity,
        DateTime $startTime,
        DateTime $finishTime,
        int $maxPrice,
        ?int $routeMaxAge = null
    ): array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->where('r.origin = :origin');
        $qb->andWhere('r.departureDay >= :startTime');
        $qb->andWhere('r.departureDay <= :finishTime');
        $qb->andWhere('r.price <= :maxPrice');
        $qb->orderBy('r.price', 'DESC');

        $qb->setParameter('origin', $startCity);
        $qb->setParameter('startTime', $startTime);
        $qb->setParameter('finishTime', $finishTime);
        $qb->setParameter('maxPrice', $maxPrice);

        $this->addAgeCondition($qb, $routeMaxAge);

        return $qb->getQuery()->useResultCache(true)->getResult();
    }

    /**
     * @param City     $startCity
     * @param DateTime $startTime
     * @param int      $maxPrice
     *
     * @return array
     */
    private function getRoutesFromCityFromPreloadedCache(City $startCity, DateTime $startTime, DateTime $finishTime, int $maxPrice): array
    {
        $result = [];
        $routesFromCity = $this->preloadedRoutes[$startCity->getCode()] ?? [];
        foreach($routesFromCity as $departureDay => $routes)
        {
            $departureDatetime = DateTime::createFromFormat(self::DATETIME_FORMAT, $departureDay);
            if($departureDatetime < $startTime || $departureDatetime > $finishTime)
            {
                continue;
            }

            foreach($routes as $route)
            {
                if($route->getPrice() > $maxPrice)
                {
                    break;
                }

                $result[] = $route;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getQueryForStatMap(): string
    {
        $query = "
            SELECT o.lat origin_lat, o.lon origin_lon, d.lat destination_lat, d.lon destination_lon, AVG(r.price) price
            FROM route r
            JOIN city o ON o.code = r.origin_id
            JOIN city d ON d.code = r.destination_id
            GROUP BY o.lat, o.lon, d.lat, d.lon
        ";
        return $query;
    }

    /**
     * @param QueryBuilder $qb
     * @param int|null $maxAgeInDays
     * @return QueryBuilder
     * @throws \Exception
     */
    private function addAgeCondition(QueryBuilder $qb, ?int $maxAgeInDays): QueryBuilder
    {
        $maxAgeInDays = $maxAgeInDays ?: Route::RELEVANCE_MAX_AGE;
        $qb->andWhere('r.savedAt >= :searchRoutesSavedFrom');
        $qb->setParameter('searchRoutesSavedFrom', new DateTime("-$maxAgeInDays days"));

        return $qb;
    }
}
