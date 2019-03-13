<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    const MIN_CITY_POPULATION_TO_USE_IT_IN_SEARCH = 400*1000;
    const MIN_CITY_PASSENGERS_CARRIED_TO_USE_IT_IN_SEARCH = 10*1000;

    const RUSSIAN_DEPARTURE_CITIES = ['KGD', 'LED', 'MOW', 'ROV'];
    const LARGEST_EUROPE_AIR_HUBS = ['AMS', 'BCN', 'LON', 'MOW', 'PAR', 'RIX', 'ROM', 'FFT'];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Return cities that worst to search routes between.
     * Returned cities are european country capitals or russian exclusive cities or european large cities or large air hubs
     *
     * @return City[]
     */
    public function getLargeEuropeCities(Country $country = null)
    {
        $qb = $this->getLargeEuropeCitiesQueryBuilder($country);

        return $qb->getQuery()->getResult();
    }

    public function getAllEuropeCities()
    {
        $qb = $this->getEuropeCitiesQueryBuilder();

        return $qb->getQuery()->getResult();
    }

    public function getEuropeLargestAirHubs()
    {
        $qb = $this->getEuropeLargestAirHubsQueryBuilder();

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Country|null $country
     *
     * @return QueryBuilder
     */
    public function getLargeEuropeCitiesQueryBuilder(Country $country = null): QueryBuilder
    {
        $qb = $this->getEuropeCitiesQueryBuilder($country);
        $qb
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->neq('city.country', ':russia_code'),
                    $qb->expr()->orX(
                        $qb->expr()->eq('city.code', 'country.capital'),
                        $qb->expr()->gte('city.population', self::MIN_CITY_POPULATION_TO_USE_IT_IN_SEARCH),
                        $qb->expr()->gte('city.passengersCarried', self::MIN_CITY_PASSENGERS_CARRIED_TO_USE_IT_IN_SEARCH)
                    )
                ),
                $qb->expr()->in('city.code', self::RUSSIAN_DEPARTURE_CITIES)
            ))
            ->setParameter('russia_code', 'RU');


        return $qb;
    }

    public function getEuropeCitiesQueryBuilder(Country $country = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('city');
        $qb
            ->leftJoin('city.country', 'country')
            ->where('country.continent = :europe')
            ->setParameter('europe', Country::CONTINENT_EUROPE)
            ->orderBy('city.name');

        if($country)
        {
            $qb->andWhere('city.country = :country')->setParameter('country', $country->getCode());
        }

        return $qb;
    }

    public function getEuropeLargestAirHubsQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('city');
        $qb
            ->leftJoin('city.country', 'country')
            ->where('country.continent = :europe')
            ->andWhere('city.code IN (:larges_air_hubs)')
            ->setParameter('europe', Country::CONTINENT_EUROPE)
            ->setParameter('larges_air_hubs', self::LARGEST_EUROPE_AIR_HUBS)
            ->orderBy('city.name');

        return $qb;
    }
}
