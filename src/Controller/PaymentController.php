<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/paiement/wave', name: 'payment_wave', methods: ['GET'])]
    public function wave(Request $request): Response
    {
        return $this->render('payment/redirect.html.twig', [
            'orderId' => $request->query->get('order'),
            'paymentName' => 'Wave',
        ]);
    }

    #[Route('/paiement/orange-money', name: 'payment_orange_money', methods: ['GET'])]
    public function orangeMoney(Request $request): Response
    {
        return $this->render('payment/redirect.html.twig', [
            'orderId' => $request->query->get('order'),
            'paymentName' => 'Orange Money',
        ]);
    }

    #[Route('/paiement/carte-bancaire', name: 'payment_card', methods: ['GET'])]
    public function card(Request $request): Response
    {
        return $this->render('payment/redirect.html.twig', [
            'orderId' => $request->query->get('order'),
            'paymentName' => 'Carte bancaire',
        ]);
    }
}
