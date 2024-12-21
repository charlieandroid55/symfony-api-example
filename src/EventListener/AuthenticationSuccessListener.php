<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

/**
 * @codeCoverageIgnore
 */
class AuthenticationSuccessListener
{
    private EntityManagerInterface $manager;

    /**
     * AuthenticationSuccessListener constructor.
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $user->setLastLogin(new \DateTime('now'));
        $this->manager->persist($user);
        $this->manager->flush();
    }
}
