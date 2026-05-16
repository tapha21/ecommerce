<?php

namespace App\Service;

use App\Repository\CategoryRepository;

class CategoryService
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    // 🔹 récupérer toutes les catégories
    public function getAllCategories()
    {
        return $this->categoryRepository->findAll();
    }

    // 🔹 récupérer une catégorie par ID
    public function getCategoryById(int $id)
    {
        return $this->categoryRepository->find($id);
    }

    // 🔹 catégories avec produits
    public function getCategoriesWithProducts()
    {
        return $this->categoryRepository->findWithProducts();
    }
}