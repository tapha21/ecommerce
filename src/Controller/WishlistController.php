<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\WishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/souhait')]
class WishlistController extends AbstractController
{
    // ❤️ GET WISHLIST (PAGE)
    #[Route('', name: 'app_wishlist_index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $wishlist = $session->get('wishlist', []);

        return $this->render('wishlist.html.twig', [
            'wishlist' => $wishlist
        ]);
    }

    // ➕ ADD TO WISHLIST
    #[Route('/add', name: 'app_wishlist_add', methods: ['POST'])]
    public function add(
        Request $request,
        ProductRepository $repo,
        WishlistService $service,
        SessionInterface $session
    ){
        $data = json_decode($request->getContent(), true);

        $product = $repo->find($data['product_id']);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $service->add($session, $product);

        return $this->json(['message' => 'added to wishlist']);
    }

    // ❌ REMOVE FROM WISHLIST
    #[Route('/remove/{id}', name: 'app_wishlist_remove', methods: ['DELETE'])]
    public function remove(
        $id,
        WishlistService $service,
        SessionInterface $session
    ){
        $service->remove($session, $id);

        return $this->json(['message' => 'removed']);
    }

    // 🧹 CLEAR WISHLIST
    #[Route('/clear', name: 'app_wishlist_clear', methods: ['DELETE'])]
    public function clear(
        WishlistService $service,
        SessionInterface $session
    ){
        $service->clear($session);

        return $this->json(['message' => 'wishlist cleared']);
    }

    // 📦 GET WISHLIST (API JSON)
    #[Route('/api', name: 'app_wishlist_api', methods: ['GET'])]
    public function api(SessionInterface $session)
    {
        return $this->json($session->get('wishlist', []));
    }
}