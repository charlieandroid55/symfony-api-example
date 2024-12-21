<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\EncryptionService;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;


/**
 * Custom JWT Encoder class that extends the Lcobucci JWTEncoder.
 * Provides encryption and decryption of JWT tokens using the EncryptionService.
 */
class JwtCustomEncoder extends LcobucciJWTEncoder
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        JWSProviderInterface $jwsProvider
    ) {
        parent::__construct($jwsProvider);
    }

    /**
     * Encodes a JWT token with the given payload and header.
     *
     * @param array $payload Payload to be encoded in the token.
     * @param array $header Optional header to be included in the token.
     *
     * @return string Encrypted JWT token.
     *
     * @throws JWTEncodeFailureException
     */
    public function encode(array $payload, array $header = [])
    {
        try {
            $token = parent::encode($payload, $header);

            $encryptedToken = $this->encryptionService->encrypt($token);
            return $encryptedToken;
        } catch (\Exception $e) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::INVALID_CONFIG, 'An error occurred while trying to encode the JWT token.', $e);
        }
    }

    /**
     * Decodes a JWT token and returns the payload.
     *
     * @param string $token Encrypted JWT token to be decoded.
     *
     * @return array Payload from the decoded token.
     *
     * @throws JWTDecodeFailureException
     */
    public function decode($token): array
    {
        try {
            $decryptedToken = $this->encryptionService->decrypt($token);
            if (!$decryptedToken) {
                throw new \RuntimeException('Token not encrypted');
            }
            
            return parent::decode($decryptedToken);
        } catch (\Exception $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token', $e);
        }
    }
}
