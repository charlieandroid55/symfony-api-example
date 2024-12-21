<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Provider\CustomUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class JsonAuthenticator implements InteractiveAuthenticatorInterface
{
    /**
     * @param array<string> $options
     */
    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly CustomUserProvider $customUserProvider,
        private readonly null|AuthenticationSuccessHandlerInterface $successHandler = null,
        private readonly null|AuthenticationFailureHandlerInterface $failureHandler = null,
        private array $options = [],
        private null|PropertyAccessorInterface $propertyAccessor = null,
        private ?TranslatorInterface $translator = null
    ) {
        $this->options = array_merge(['username_path' => 'username', 'password_path' => 'password'], $options);
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    public function supports(Request $request): ?bool
    {
        if (!str_contains($request->getRequestFormat() ?? '', 'json') && !str_contains($request->getContentTypeFormat() ?? '', 'json')) {
            return false;
        }

        if (isset($this->options['check_path']) && !$this->httpUtils->checkRequestPath($request, $this->options['check_path'])) {
            return false;
        }

        return true;
    }

    /**
     * @throws \JsonException
     */
    public function authenticate(Request $request): Passport
    {
        try {
            $credentials = $this->getCredentials($request);
        } catch (BadRequestHttpException|\JsonException $e) {
            $request->setRequestFormat('json');
            throw $e;
        }

        $passport = new Passport(
            new UserBadge($credentials['username'], $this->customUserProvider->loadUserByIdentifier(...)),
            new PasswordCredentials($credentials['password'])
        );
        if ($this->customUserProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->customUserProvider));
        }

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->successHandler?->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if (null === $this->failureHandler) {
            if (null !== $this->translator) {
                $errorMessage = $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security');
            } else {
                $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
            }

            return new JsonResponse(['error' => $errorMessage], Response::HTTP_UNAUTHORIZED);
        }

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    public function isInteractive(): bool
    {
        return true;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * @return array<string>
     */
    private function getCredentials(Request $request): array
    {
        $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        if (!$data instanceof \stdClass) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        $credentials = [];
        try {
            $credentials['username'] = $this->propertyAccessor->getValue($data, $this->options['username_path']);

            if (!\is_string($credentials['username'])) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->options['username_path']));
            }

            if (\strlen($credentials['username']) > UserBadge::MAX_USERNAME_LENGTH) {
                throw new BadCredentialsException('Invalid username.');
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->options['username_path']), $e);
        }

        try {
            $credentials['password'] = $this->propertyAccessor->getValue($data, $this->options['password_path']);

            if (!\is_string($credentials['password'])) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->options['password_path']));
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->options['password_path']), $e);
        }

        return $credentials;
    }
}
