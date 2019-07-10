<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\FlightAvgPriceSubscribe;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
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
     * @param int       $maxPercent
     * @param City|null $origin
     * @param City|null $destination
     *
     * @return FlightAvgPriceSubscribe[]
     */
    public function getSubscribers(int $maxPercent, DateTime $departureDay, ?City $origin, ?City $destination): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where('s.priceDropPercent <= :maxPercent');
        $qb->andWhere('(s.origin = :origin OR s.origin IS NULL)');
        $qb->andWhere('(s.destination = :destination OR s.origin IS NULL)');
        $qb->andWhere('(s.from <= :departureDay OR s.from IS NULL)');
        $qb->andWhere('(s.to >= :departureDay OR s.to IS NULL)');

        $qb->setParameter('maxPercent', $maxPercent, Type::FLOAT);
        $qb->setParameter('origin', $origin->getCode());
        $qb->setParameter('destination', $destination->getCode());
        $qb->setParameter('departureDay', $departureDay, Type::DATE);

        return $qb->getQuery()->getResult();
    }
}
