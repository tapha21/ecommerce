<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // 🔹 LISTE DES CATÉGORIES
    #[Route('/categories', name: 'app_category_index')]
    public function index(): Response
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    // 🔹 DETAIL D’UNE CATÉGORIE (⚡ CELLE QUI TE MANQUE)
    #[Route('/category/{id}', name: 'app_category_show')]
    public function show(int $id): Response
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }

#[Route('/create', name: 'app_category_create', methods: ['POST'])]
public function create(
    Request $request,
    EntityManagerInterface $em
): Response
{
    $data = $request->request->all('category');

    $category = new Category();

    $category->setNom($data['nom']);

    $em->persist($category);

    $em->flush();

    $this->addFlash(
        'success',
        'Catégorie ajoutée avec succès'
    );

    return $this->redirectToRoute('admin_categories');
}
#[Route('/category/edit/{id}', name: 'app_category_edit', methods: ['POST'])]
public function edit(
    int $id,
    Request $request,
    EntityManagerInterface $em,
    CategoryRepository $repo
): Response {

    $category = $repo->find($id);

    if (!$category) {
        throw $this->createNotFoundException();
    }

    $category->setNom($request->request->get('nom'));

    $em->flush();

    $this->addFlash('success', 'Catégorie modifiée');

    return $this->redirectToRoute('admin_categories');
}
#[Route('/category/delete/{id}', name: 'app_category_delete', methods: ['POST'])]
public function delete(
    int $id,
    EntityManagerInterface $em,
    CategoryRepository $repo
): Response {

    $category = $repo->find($id);

    if (!$category) {
        throw $this->createNotFoundException();
    }

    $em->remove($category);
    $em->flush();

    $this->addFlash('success', 'Catégorie supprimée');

    return $this->redirectToRoute('admin_categories');
}
}