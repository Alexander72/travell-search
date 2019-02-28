<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Return cities that worst to search routes between.
     * Returned cities are european country capitals or russian exclusive cities or european large cities
     *
     * @return mixed
     */
    public function getLargeEuropeCities()
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->leftJoin('c.country', 'country')
            ->where('country.continent = :europe')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->neq('c.country', ':russia_code'),
                    $qb->expr()->orX(
                        $qb->expr()->eq('c.country', 'country.capital'),
                        $qb->expr()->gte('c.population', self::MIN_CITY_POPULATION_TO_USE_IT_IN_SEARCH)
                    )
                ),
                $qb->expr()->in('c.code', ['KGD','LED','MOW','ROV'])
            ))
            ->setParameter('europe', Country::CONTINENT_EUROPE)
            ->setParameter('russia_code', 'RU');

        return $qb->getQuery()->getResult();
    }
}
