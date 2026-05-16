<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // 🔥 PRODUITS EN PROMO
    public function findPromotions()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.promotion = true')
            ->getQuery()
            ->getResult();
    }

    // 🆕 NOUVEAUTÉS
    public function findNouveautes()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nouveaute = true')
            ->getQuery()
            ->getResult();
    }

    // ⭐ BEST SELLERS
    public function bestSellingProducts()
{
    return $this->createQueryBuilder('p')
        ->select('p, SUM(i.quantity) as totalSold')
        ->join('App\Entity\OrderItem', 'i', 'WITH', 'i.product = p')
        ->groupBy('p.id')
        ->orderBy('totalSold', 'DESC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
}

    // 📂 PAR CATÉGORIE
    public function findByCategory($categoryId)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :cat')
            ->setParameter('cat', $categoryId)
            ->getQuery()
            ->getResult();
    }

    // 💰 FILTRE PRIX
    public function findByPriceRange($min, $max)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.prix BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->getQuery()
            ->getResult();
    }

    // 🔍 RECHERCHE
    public function search($keyword)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.titre LIKE :k')
            ->setParameter('k', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();
    }
    public function findProductsByCategoryName(string $categoryName): array
{
    return $this->createQueryBuilder('p')
        ->join('p.category', 'c')
        ->where('c.nom = :nom')
        ->setParameter('nom', $categoryName)
        ->setMaxResults(10)
        ->getQuery()
     
        ->getResult();
}

public function findAllWithLots()
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.lots', 'l')
        ->addSelect('l')
        ->getQuery()
        ->getResult();
}
}