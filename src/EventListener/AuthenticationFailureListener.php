<?php

declare(strict_types=1);

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

/**
 * @codeCoverageIgnore
 */
class AuthenticationFailureListener
{
    private const INVALID_CREDENTIALS = 'Invalid credentials.';

    /**
     * AuthenticationSuccessListener constructor.
     */
    public function __construct(
        //        private readonly EntityManagerInterface $manager
    ) {
    }

    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $data = [
            'status' => 401,
            'message' => 'Unauthorized',
        ];
        $messageKey = $event->getException()->getMessageKey();

        if (self::INVALID_CREDENTIALS === $messageKey) {
            $data['message'] = 'InvalidCredentials';
        }

        $response = new JWTAuthenticationFailureResponse($data['message'], $data['status']);

        $event->setResponse($response);
    }
}
