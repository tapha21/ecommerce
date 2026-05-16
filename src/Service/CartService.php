<?php
namespace App\Service;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    public function __construct(
        private CartRepository $repo,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private UserRepository $userRepo
    ) {}
    private function getCart(SessionInterface $session)
    {
        return $session->get('cart', []);
    }

    // 👤 GET USER FROM SESSION
   private function getUser(): ?User
{
    $userId = $this->requestStack->getSession()->get('user_id');

    return $userId ? $this->userRepository->find($userId) : null;
}

    // ➕ ADD TO CART
   public function add(SessionInterface $session, Product $product, int $qty = 1)
    {
        $cart = $this->getCart($session);

        $id = $product->getId();

        if (isset($cart[$id])) {
            $cart[$id] += $qty;
        } else {
            $cart[$id] = $qty;
        }

        $session->set('cart', $cart);
    }

    public function remove(SessionInterface $session, int $productId)
    {
        $cart = $this->getCart($session);

        unset($cart[$productId]);

        $session->set('cart', $cart);
    }

    public function clear(SessionInterface $session)
    {
        $session->remove('cart');
    }

    // 🔄 UPDATE QUANTITY
    public function updateQuantity(Cart $cart, int $qty): void
    {
        if ($qty <= 0) {
            $this->em->remove($cart);
        } else {
            $cart->setQuantity($qty);
        }

        $this->em->flush();
    }
}