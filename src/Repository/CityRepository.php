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
    const LARGEST_EUROPE_AIR_HUBS = ['AMS', 'BCN', 'LON', 'MOW', 'PAR', 'RIX', 'ROM', 'FFT', 'BER'];
    /**
     * Список городов, из которых что-то летает в крупные хабы LARGEST_EUROPE_AIR_HUBS
     * Проверено для всех городов европы. Поиск производился в марте на июнь 2019 года
     */
    const EUROPE_CITIES_WORTH_TO_USE_IN_SEARCH = [
        'HEL', 'BER', 'MSQ', 'TBS', 'BCN', 'MIL', 'PAR', 'RIX', 'PRG', 'VNO', 'ATH', 'IST', 'WAW', 'AMS', 'MAD', 'LON',
        'ROM', 'LIS', 'VCE', 'MUC', 'BUD', 'TLL', 'EVN', 'SOF', 'NCE', 'VIE', 'KUT', 'AGP', 'BRU', 'OPO', 'DUB', 'TCI',
        'BAK', 'PMI', 'BUH', 'CTA', 'VAR', 'BOJ', 'IBZ', 'TIV', 'PMO', 'AYT', 'BUS', 'NAP', 'FRA', 'LCA', 'ALC', 'ZRH',
        'LYS', 'GDN', 'CPH', 'EDI', 'HAM', 'DUS', 'STO', 'OSL', 'BLQ', 'VLC', 'ZAG', 'GVA', 'DRS', 'SKG', 'HAJ', 'SPU',
        'HER', 'STR', 'MRS', 'RHO', 'GOA', 'CFU', 'PSA', 'GZP', 'BEG', 'BTS', 'VRN', 'MLA', 'LJU', 'BRI', 'REK', 'ANK',
        'BOD', 'OLB', 'PUY', 'RMI', 'CGN', 'LPA', 'GRO', 'CAG', 'FLR', 'IZM', 'SVQ', 'KRK', 'FNC', 'DLM', 'MAN', 'PFO',
        'JTR', 'DBV', 'TRN', 'TGD', 'TLS', 'LEJ', 'TIA', 'SXB', 'ADA', 'SZG', 'INN', 'FKB', 'SKP', 'BIO', 'CHQ', 'KGS',
        'SUF', 'BJV', 'ECN', 'DEB', 'LWN', 'KVD', 'FAO', 'TRS', 'EIN', 'KLV', 'FMM', 'OST', 'MJT', 'PLQ', 'PSR', 'SCV',
        'LEI', 'AJA', 'REU', 'TKU', 'JMK', 'RJK', 'NUE', 'WRO', 'OVD', 'ZTH', 'BFS', 'GLA', 'FSC', 'BRQ', 'EAP', 'ZAD',
        'RTM', 'SCQ', 'PGF', 'LPL', 'LUX', 'POZ', 'KVA', 'KUN', 'AJI', 'ACE', 'TAY', 'LIL', 'ANR', 'TRD', 'DOL', 'ASR',
        'NAV', 'BZG', 'AHO', 'GRZ', 'UME', 'DTM', 'VGO', 'BZR', 'LLK'
    ];

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
            ->leftJoin(Country::class, 'country', Join::WITH, 'country.code = city.country')
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

    public function getEuropeCitiesForSearch(): array
    {
        $qb = $this->getEuropeCitiesForSearchQueryBuilder();
        return $qb->getQuery()->getResult();
    }

    public function getEuropeCitiesForSearchQueryBuilder(): QueryBuilder
    {
        $cities = $this->getEuropeCityCodesWorthToUseInSearch();

        $qb = $this->getEuropeCitiesQueryBuilder();
        $qb->andWhere('city.code IN (:cities)');
        $qb->setParameter('cities', $cities);

        return $qb;
    }

    /**
     * @return array
     */
    private function getEuropeCityCodesWorthToUseInSearch(): array
    {
        return \array_merge(self::RUSSIAN_DEPARTURE_CITIES, self::LARGEST_EUROPE_AIR_HUBS, self::EUROPE_CITIES_WORTH_TO_USE_IN_SEARCH);
    }
}
