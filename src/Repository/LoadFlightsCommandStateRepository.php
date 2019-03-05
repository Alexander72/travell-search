<?php

namespace App\Repository;

use App\Entity\LoadFlightsCommandState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LoadFlightsCommandState|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoadFlightsCommandState|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoadFlightsCommandState[]    findAll()
 * @method LoadFlightsCommandState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoadFlightsCommandStateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LoadFlightsCommandState::class);
    }

    /**
     * @param \DateTime $departMonthFirstDay
     *
     * @return LoadFlightsCommandState|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLoadMultipleFlightsCommandState(\DateTime $departMonthFirstDay): ?LoadFlightsCommandState
    {
        $qb = $this->createQueryBuilder('ls');
        $qb->where('ls.status != :finished_status AND ls.type = :load_flights_type AND ls.departMonthFirstDay = :departMonthFirstDay');
        $qb->setParameters([
            'finished_status' => LoadFlightsCommandState::STATUS_FINISHED,
            'load_flights_type' => LoadFlightsCommandState::TYPE,
            'departMonthFirstDay' => $departMonthFirstDay->format('Y-m-d'),
        ]);
        $qb->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
