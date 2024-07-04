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
}
