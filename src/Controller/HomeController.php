<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        AuthService $authService

    ): Response {
        // 1. Récupérer toutes les catégories pour le carrousel du haut
        $categories = $categoryRepository->findAll();

        // 2. Les 3 derniers produits pour la sidebar
        $lastProducts = $productRepository->findBy([], ['id' => 'DESC'], 3);

        // 3. Produits phares
        $featuredProducts = $productRepository->findBy(['phares' => true], ['id' => 'DESC'], 6);
       $promoProducts = $productRepository->findBy([
            'promotion' => true
        ], ['id' => 'DESC']);

        // 4. Produits par catégories spécifiques
        // Note: Remplace 'nom' par le nom exact de ton champ en base de données
        $boxCadeaux = $productRepository->findProductsByCategoryName('Box Cadeaux');
        $islamicBooks = $productRepository->findProductsByCategoryName('Livres Islamiques');
        $scienceBooks = $productRepository->findProductsByCategoryName('Science');


        return $this->render('index.html.twig', [
            'categories' => $categories,
            'lastProducts' => $lastProducts,
            'featuredProducts' => $featuredProducts,
            'boxCadeaux' => $boxCadeaux,
            'islamicBooks' => $islamicBooks,
            'scienceBooks' => $scienceBooks,
            'promoProducts' => $promoProducts,
        ]);
    }
}
