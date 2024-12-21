<?php

declare(strict_types=1);

namespace App\Service;

class EncryptionService
{
    public function encrypt(string $text): string
    {
        return base64_encode($text);
    }

    public function decrypt(string $text): string
    {
        return base64_decode($text);
    }
}
