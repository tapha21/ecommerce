<?php
namespace App\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(private RequestStack $requestStack) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $session = $this->requestStack->getSession();

        if ($session) {
            $session->getFlashBag()->add('error', 'Email ou mot de passe incorrect');
        }
    }
}