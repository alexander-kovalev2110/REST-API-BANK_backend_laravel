<?php

namespace App\Infrastructure\Security;

use App\Domain\Customer\Customer;
use App\Application\Customer\Service\TokenServiceInterface;
use Firebase\JWT\JWT;

final class JWTTokenService implements TokenServiceInterface
{
    public static function getSecret(): string
    {
        return config('app.key', 'SecretKeyForJWTAuthenticationInRESTApiBank2026');
    }

    public function createToken(Customer $customer): string
    {
        $payload = [
            'sub' => $customer->getId(),
            'name' => $customer->getName(),
            'username' => $customer->getName(),
            'iat' => time(),
            'exp' => time() + 3600
        ];

        return JWT::encode($payload, self::getSecret(), 'HS256');
    }
}