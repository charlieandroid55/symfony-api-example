<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Security\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @codeCoverageIgnore
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->getEnabled()) {
            throw new CustomUserMessageAccountStatusException('NOT_ACTIVE_USER');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        //perform some logic.
    }
}
