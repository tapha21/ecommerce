<?php
namespace App\Controller;

use App\Entity\Orders;
use App\Enum\OrderStatus;
use App\Enum\PaymentMethod;
use App\Repository\OrdersRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OrderService;
use  App\Service\PaymentService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class OrderController extends AbstractController
{
    private OrderService $orderService;
    private PaymentService $paymentService;

    public function __construct(
        OrderService $orderService,
        PaymentService $paymentService
    ) {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }
    // 💳 CHECKOUT
   #[Route('/checkout', name: 'checkout_page', methods: ['GET'])]
    public function checkoutPage()
    {
        return $this->render('checkout.html.twig');
    }
        #[Route('/checkout', methods: ['POST'])]
        public function checkout(Request $request)
        {
            $data = json_decode($request->getContent(), true);

            $items = $data['items'] ?? [];
            $payment = PaymentMethod::tryFrom($data['payment'] ?? '') ?? PaymentMethod::CashOnDelivery;
            $userData = $data['user'] ?? null;

            $order = $this->orderService->checkout(
                null,
                $items,
                $payment,
                $userData
            );

            $paymentResult = $this->paymentService->process($order, $payment);

            return $this->json($paymentResult);
        }
    // 👤 CLIENT ORDERS
    #[Route('/my', methods: ['GET'])]
public function myOrders(OrdersRepository $repo)
{
    return $this->json(
        $repo->findByUserWithItems($this->getUser())
    );
}

    // 👤 ORDER DETAIL
    #[Route('/my/{id}',name: 'me', methods: ['GET'])]
    public function myOrderDetail(OrdersRepository $repo, $id)
    {
        $user = $this->getUser();
        $order = $repo->find($id);
        if (!$order || $order->getUser() !== $user) {
            return $this->json(['error' => 'Forbidden'], 403);
        }
        return $this->json($order);
    }

    // 🛠️ ADMIN ALL ORDERS
    #[Route('/admin', methods: ['GET'])]
    public function allOrders(OrdersRepository $repo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json($repo->findAll());
    }

    // 🛠️ ADMIN CANCEL ORDER (SAFE VERSION)
    #[Route('/admin/{id}/cancel', methods: ['POST'])]
    public function cancel(
        Orders $order,
        OrderService $service
    ){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $service->cancelOrder($order);

        return $this->json(['message' => 'order cancelled']);
    }
   #[Route('/admin/{id}/status', name: 'admin_order_status', methods: ['POST', 'PATCH'])]
public function changeStatus(
    Orders $order,
    Request $request,
    EntityManagerInterface $em
) {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $status = $request->request->get('status');

    if (!$status) {
        throw new \Exception('Status manquant');
    }

    // ✅ CONVERSION STRING -> ENUM
    $orderStatus = OrderStatus::from($status);

    $order->setStatus($orderStatus);

    $em->flush();

    return $this->redirectToRoute('admin_dashboard');
}
#[Route('/api/admin/dashboard', methods: ['GET'])]
public function dashboard(
    OrdersRepository $ordersRepo,
    UserRepository $userRepo,
    ProductRepository $productRepo
){
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    return $this->json([
        'total_users' => $userRepo->countUsers(),
        'total_orders' => $ordersRepo->countOrders(),
        'total_sales' => $ordersRepo->totalSales(),
        'best_sellers' => $productRepo->bestSellingProducts(),
    ]);
}
}
