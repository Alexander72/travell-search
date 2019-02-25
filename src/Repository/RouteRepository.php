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
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Route::class);
    }

    /**
     * @param City     $startCity
     * @param DateTime $startTime
     * @param DateTime $finishTime
     * @param int      $maxPrice
     *
     * @return mixed
     */
    public function getRoutesFromCity(City $startCity, DateTime $startTime, DateTime $finishTime, int $maxPrice)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.origin = :origin');
        $qb->andWhere('c.departure_day >= :startTime');
        $qb->andWhere('c.departure_day <= :finishTime');
        $qb->andWhere('c.cost <= :maxPrice');

        $qb->setParameters(new ArrayCollection([
            new Parameter('origin', $startCity),
            new Parameter('startTime', $startTime),
            new Parameter('finishTime', $finishTime),
            new Parameter('maxPrice', $maxPrice),
        ]));

        return $qb->getQuery()->getResult();
    }
}
