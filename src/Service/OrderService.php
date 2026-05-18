<?php

namespace App\Service;

use App\Entity\OrderItem;
use App\Entity\Orders;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Enum\PaymentMethod;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepository $cartRepo,
        private RequestStack $requestStack
    ) {}

    // 👤 GET USER FROM SESSION
    private function getUser(): ?User
    {
        $session = $this->requestStack->getSession();
        $userId = $session->get('user_id');

        if (!$userId) return null;

        return $this->em->getRepository(User::class)->find($userId);
    }

    // 💳 CHECKOUT
public function checkout(?User $user, array $items, PaymentMethod $payment = PaymentMethod::CashOnDelivery, ?array $guestData = null)
{
    $order = new Orders();
    $order->setUser($user);
    if ($user === null && $guestData) {
    $order->setGuestName($guestData['name'] ?? null);
    $order->setGuestPhone($guestData['phone'] ?? null);
    $order->setGuestAddress($guestData['address'] ?? null);
    }
    $order->setCreatedAt(new \DateTimeImmutable());
    $order->setStatus(OrderStatus::En_Attente);
    $order->setPaymentMethod($payment);

    $total = 0;

    foreach ($items as $itemData) {

        $product = $this->em->getRepository(Product::class)->find($itemData['id']);

        if (!$product) {
            throw new \Exception("Produit introuvable ID: ".$itemData['id']);
        }

        $qty = max(1, $itemData['qty'] ?? 1);

        if ($product->getStock() < $qty) {
            throw new \Exception("Stock insuffisant pour ".$product->getTitre());
        }

        $product->setStock($product->getStock() - $qty);

        $item = new OrderItem();
        $item->setOrder($order);
        $item->setProductName($product->getTitre());
        $item->setProduct($product);
        $item->setQuantity($qty);
        $unitPrice = $this->getUnitPrice($product, $qty);
        $item->setPrice($unitPrice);
        $total += $unitPrice * $qty;
        $this->em->persist($item);
    }

    $order->setTotal($total);

    $this->em->persist($order);
    $this->em->flush();

    return $order;
}
public function cancelOrder(Orders $order)
{
    if ($order->getStatus() === OrderStatus::Annuler) {
        throw new \Exception("Already cancelled");
    }

    foreach ($order->getItems() as $item) {

        $product = $item->getProduct();

        // 🔁 rollback stock
        $product->setStock(
            $product->getStock() + $item->getQuantity()
        );
    }

    $order->setStatus(OrderStatus::Annuler);

    $this->em->flush();
}
private function getUnitPrice(Product $product, int $qty): float
{
    $bestPrice = $product->getPrix();

    foreach ($product->getLots() as $lot) {

        if ($qty >= $lot->getQuantite()) {
            $bestPrice = $lot->getPrix();
        }
    }

    return $bestPrice;
}
}
