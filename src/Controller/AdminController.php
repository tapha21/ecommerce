<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        OrdersRepository $ordersRepo,
        UserRepository $userRepo,
        ProductRepository $productRepo
    ){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dashboard.html.twig', [
            'total_users' => $userRepo->countUsers(),
            'total_orders' => $ordersRepo->countOrders(),
            'total_sales' => $ordersRepo->totalSales(),
            'products' => $productRepo->findAll(),
            'orders' => $ordersRepo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }
    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('users.html.twig', [
            'users' => $userRepo->findBy([], ['id' => 'DESC'])
        ]);
    }
#[Route('/products', name: 'admin_products')]
public function products(ProductRepository $productRepo)
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    return $this->render('products.html.twig', [
        'products' => $productRepo->findBy([], ['id' => 'DESC'])
    ]);
}
#[Route('/categories', name: 'admin_categories')]
public function categories(CategoryRepository $categoryRepo)
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    return $this->render('categories.html.twig', [
        'categories' => $categoryRepo->findBy([], ['id' => 'DESC'])
    ]);
}
}