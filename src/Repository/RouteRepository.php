<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Route;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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

    public function preloadRoutes(DateTime $startTime, DateTime $finishTime, int $maxPrice)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.departureDay >= :startTime');
        $qb->andWhere('r.departureDay <= :finishTime');
        $qb->andWhere('r.price <= :maxPrice');
        $qb->orderBy('r.departureDay');
        $qb->orderBy('r.price');

        $qb->setParameters(new ArrayCollection([
            new Parameter('startTime', $startTime),
            new Parameter('finishTime', $finishTime),
            new Parameter('maxPrice', $maxPrice),
        ]));

        $queryResult = $qb->getQuery()->getResult();
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
     * @param City     $startCity
     * @param DateTime $startTime
     * @param DateTime $finishTime
     * @param int      $maxPrice
     *
     * @return array
     */
    private function getRoutesFromCityFromDB(City $startCity, DateTime $startTime, DateTime $finishTime, int $maxPrice): array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->where('r.origin = :origin');
        $qb->andWhere('r.departureDay >= :startTime');
        $qb->andWhere('r.departureDay <= :finishTime');
        $qb->andWhere('r.price <= :maxPrice');

        $qb->setParameters(new ArrayCollection([
            new Parameter('origin', $startCity),
            new Parameter('startTime', $startTime),
            new Parameter('finishTime', $finishTime),
            new Parameter('maxPrice', $maxPrice),
        ]));

        return $qb->getQuery()->useResultCache(true)->useResultCache(true)->getResult();
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
}
