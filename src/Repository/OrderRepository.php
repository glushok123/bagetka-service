<?php

namespace App\Repository;

use App\Dto\Order\Filter\FilterDto;
use App\Entity\Order;
use App\Enum\CommercialOfferStatus;
use App\Enum\OfficeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends AbstractBasicRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getCollection(
        $dateStart, $dateEnd, $officeType
    ): array
    {
        $builder =  $this->createQueryBuilder('c')
            ->select('c');


        if (!empty($officeType)){
            $builder = $builder->andWhere('c.officeType = (:officeType)')->setParameter('officeType', OfficeType::from($officeType));
        }

        if (!empty($dateStart)){
            $builder = $builder->andWhere('c.createdAt >= (:dateStart)')->setParameter('dateStart', $dateStart);
        }


        if (!empty($dateEnd)){
            $builder = $builder->andWhere('c.createdAt <= (:dateEnd)')->setParameter('dateEnd', $dateEnd);
        }

        return $builder->getQuery()->getResult();
    }

    public function getTotalDurationHoursByDay(\DateTimeImmutable $date, OfficeType $officeType, ?int $excludeOrderId = null): int
    {
        $start = $date->setTime(0, 0, 0);
        $end = $date->setTime(23, 59, 59);

        $builder = $this->createQueryBuilder('o')
            ->select('COALESCE(SUM(o.durationHours), 0)')
            ->andWhere('o.officeType = :officeType')
            ->andWhere('o.createdAt >= :start')
            ->andWhere('o.createdAt <= :end')
            ->andWhere('o.isDeleted = false')
            ->setParameter('officeType', $officeType)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($excludeOrderId !== null) {
            $builder->andWhere('o.id != :excludeId')
                ->setParameter('excludeId', $excludeOrderId);
        }

        return (int) $builder->getQuery()->getSingleScalarResult();
    }
}
