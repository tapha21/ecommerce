<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    // 🛒 Récupérer tout le panier d’un user
    public function findUserCart(User $user): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
        ->setParameter('user', $user)
        ->join('c.product', 'p')
        ->addSelect('p')
        ->getQuery()
        ->getResult();
}

    // 🔍 Vérifier si produit déjà dans panier
    public function findOneByUserAndProduct(User $user, Product $product): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // 💰 Calcul total panier
    public function getCartTotal(User $user): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.quantity * p.prix) as total')
            ->join('c.product', 'p')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }

    // 🧹 Vider panier user
    public function clearUserCart(User $user): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}