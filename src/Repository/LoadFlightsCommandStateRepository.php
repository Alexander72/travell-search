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
     * @return LoadFlightsCommandState|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLoadMultipleFlightsCommandState(): ?LoadFlightsCommandState
    {
        $qb = $this->createQueryBuilder('ls');
        $qb->where('ls.status != :finished_status AND ls.type = :load_flights_type');
        $qb->setParameters([
            'finished_status' => LoadFlightsCommandState::STATUS_FINISHED,
            'load_flights_type' => LoadFlightsCommandState::TYPE,
        ]);
        $qb->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
