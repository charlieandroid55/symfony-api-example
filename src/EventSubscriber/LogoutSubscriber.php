<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Security\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManagerInterface
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        // get the security token of the session that is about to be logged out
        $token = $event->getToken();
        $user = $token->getUser();

        // delete all staged tokens for current user
        if (null !== $user?->getUserIdentifier()) {
            $this->entityManagerInterface
                ->getRepository(RefreshToken::class)
                ->createQueryBuilder('rt')
                ->delete()
                ->where('rt.username = :userIdentifier')
                ->setParameter('userIdentifier', $user->getUserIdentifier())
                ->getQuery()
                ->execute()
            ;
        }
    }
}
