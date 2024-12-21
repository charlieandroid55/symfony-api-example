<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Security\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @codeCoverageIgnore
 */
class JWTCreatedListener
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $payload['user'] = $user->getJWTInfo();
        $event->setData($payload);
    }
}
