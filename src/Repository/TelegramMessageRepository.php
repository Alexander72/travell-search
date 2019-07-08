<?php

namespace App\Repository;

use App\Entity\TelegramMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TelegramMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramMessage[]    findAll()
 * @method TelegramMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TelegramMessage::class);
    }

    // /**
    //  * @return TelegramMessage[] Returns an array of TelegramMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TelegramMessage
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
