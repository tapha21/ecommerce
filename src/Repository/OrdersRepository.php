<?php

namespace App\Repository;

use App\Entity\Orders;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    // 📦 HISTORIQUE CLIENT
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 📊 ADMIN - TOTAL COMMANDES
    public function countOrders()
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o.id)')
        ->getQuery()
        ->getSingleScalarResult();
}

    public function findByUserWithItems(User $user)
{
    return $this->createQueryBuilder('o')
        ->leftJoin('o.items', 'i')
        ->addSelect('i')
        ->andWhere('o.user = :user')
        ->setParameter('user', $user)
        ->orderBy('o.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

    public function totalSales()
{
    return $this->createQueryBuilder('o')
        ->select('SUM(o.total)')
        ->getQuery()
        ->getSingleScalarResult();
}


}