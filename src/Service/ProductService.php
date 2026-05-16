<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(array $data, $imageUrl = null)
    {
        $product = new Product();

        $product->setTitre($data['titre']);
        $product->setDescription($data['description']);
        $product->setPrix($data['prix']);
        $product->setStock($data['stock']);
        $product->setPromotion($data['promotion'] ?? false);
        $product->setNouveaute($data['nouveaute'] ?? false);
        $product->setBestSeller($data['bestSeller'] ?? false);

        if ($imageUrl) {
            $product->setImage($imageUrl);
        }

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }
     public function decreaseStock(Product $product, int $qty)
    {
        $stock = $product->getStock() - $qty;

        if ($stock < 0) {
            throw new \Exception("Stock insuffisant");
        }

        $product->setStock($stock);

        $this->em->flush();
    }

    // ➕ augmenter stock (admin)
    public function increaseStock(Product $product, int $qty)
    {
        $product->setStock($product->getStock() + $qty);

        $this->em->flush();
    }
    public function setStock(Product $product, int $stock)
{
    if ($stock < 0) {
        throw new \Exception("Stock invalide");
    }

    $product->setStock($stock);

    $this->em->flush();
}
}