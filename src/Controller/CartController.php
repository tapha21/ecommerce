<?php
namespace App\Controller;

use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    // 🛒 GET CART
    // #[Route('', methods: ['GET'])]
    // public function index(SessionInterface $session)
    // {
    //     return $this->json($session->get('cart', []));
    // }
    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        return $this->render('cart.html.twig', [
            'cart' => $cart
        ]);
    }

    #[Route('/add', methods: ['POST'])]
    public function add(
        Request $request,
        ProductRepository $repo,
        CartService $service,
        SessionInterface $session
    ){
        $data = json_decode($request->getContent(), true);

        $product = $repo->find($data['product_id']);

        $service->add($session, $product, $data['qty'] ?? 1);

        return $this->json(['message' => 'added']);
    }

    #[Route('/remove/{id}', methods: ['DELETE'])]
    public function remove(
        $id,
        CartService $service,
        SessionInterface $session
    ){
        $service->remove($session, $id);

        return $this->json(['message' => 'removed']);
    }

    #[Route('/clear', methods: ['DELETE'])]
    public function clear(CartService $service, SessionInterface $session)
    {
        $service->clear($session);

        return $this->json(['message' => 'cart cleared']);
    }

    // 🔄 UPDATE QUANTITY
    #[Route('/update/{id}', methods: ['PUT'])]
    public function update(
        $id,
        Request $request,
        CartRepository $repo,
        CartService $service
    )
    {
        $cart = $repo->find($id);

        if (!$cart) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $service->updateQuantity($cart, $data['quantity']);

        return $this->json(['message' => 'updated']);
    }

  #[Route('/api', name: 'cart_api', methods: ['GET'])]
    public function getCart(SessionInterface $session, ProductRepository $repo)
    {
        $cart = $session->get('cart', []);

        $result = [];

        foreach ($cart as $id => $item) {

            $product = $repo->find($id);
            if (!$product) continue;

            $result[] = [
                'id' => $id,
                'titre' => $product->getTitre(),
                'image' => $product->getImage(),
                'prix' => $product->getPrix(),
                // ⚠️ IMPORTANT : on stocke qty + lot choisi
                'qty' => $item['qty'] ?? 1,
                'selectedLot' => $item['selectedLot'] ?? null,

                'lots' => array_map(function ($lot) {
                    return [
                        'id' => $lot->getId(),
                        'nom' => $lot->getNom(),
                        'quantite' => $lot->getQuantite(),
                        'prix' => $lot->getPrix(),
                    ];
                }, $product->getLots()->toArray())
            ];
        }

        return $this->json($result);
    }
 #[Route('/update', methods: ['POST'])]
    public function getupdate(Request $request, SessionInterface $session)
    {
        $data = json_decode($request->getContent(), true);

        $cart = $session->get('cart', []);

        $id = $data['product_id'];

        if (isset($cart[$id])) {
            $cart[$id]['qty'] = $data['quantity'];

            if (isset($data['selectedLot'])) {
                $cart[$id]['selectedLot'] = $data['selectedLot'];
            }
        }

        $session->set('cart', $cart);

        return $this->json(['message' => 'updated']);
    }

    
}