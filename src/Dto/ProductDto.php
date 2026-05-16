<?php
namespace App\Dto;

class ProductDto
{
    public string $titre;
    public string $description;
    public float $prix;
    public int $stock;
    public int $categoryId;
    public bool $promotion;
    public bool $nouveaute;
    public bool $bestSeller;
}