<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    public function getGlobals(): array
    {
        return [
            'categories' => $this->categoryRepository->findAll(),
        ];
    }
}