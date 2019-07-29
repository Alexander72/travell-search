<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\FlightAvgPriceSubscribe;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\Expr;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FlightAvgPriceSubscribe|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlightAvgPriceSubscribe|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlightAvgPriceSubscribe[]    findAll()
 * @method FlightAvgPriceSubscribe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlightAvgPriceSubscribeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FlightAvgPriceSubscribe::class);
    }

    /**
     * @param int|null  $maxPercent
     * @param int       $maxPrice
     * @param DateTime  $departureDay
     * @param City|null $origin
     * @param City|null $destination
     *
     * @return FlightAvgPriceSubscribe[]
     */
    public function getSubscribers(?int $maxPercent, int $maxPrice, DateTime $departureDay, ?City $origin, ?City $destination): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where($qb->expr()->orX(
            !is_null($maxPercent) ? 's.priceDropPercent <= :maxPercent' : '1=0',
            's.price >= :maxPrice'
        ));
        $qb->andWhere('(s.origin = :origin OR s.origin IS NULL)');
        $qb->andWhere('(s.destination = :destination OR s.destination IS NULL)');
        $qb->andWhere('(s.from <= :departureDay OR s.from IS NULL)');
        $qb->andWhere('(s.to >= :departureDay OR s.to IS NULL)');

        if(!is_null($maxPercent))
        {
            $qb->setParameter('maxPercent', $maxPercent);
        }

        $qb->setParameter('maxPrice', $maxPrice);
        $qb->setParameter('origin', $origin->getCode());
        $qb->setParameter('destination', $destination->getCode());
        $qb->setParameter('departureDay', $departureDay, Type::DATE);

        return $qb->getQuery()->getResult();
    }
}
