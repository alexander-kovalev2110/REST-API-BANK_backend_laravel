<?php

namespace App\Infrastructure\Middleware;

use App\Domain\Customer\Customer;
use App\Infrastructure\Security\JWTTokenService;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JWTAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return response()->json(['error' => 'JWT Token not found'], 401);
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key(JWTTokenService::getSecret(), 'HS256'));
            $customer = Customer::find($decoded->sub);

            if (!$customer) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $request->attributes->set('current_customer', $customer);
            auth()->setUser($customer);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid or expired JWT Token'], 401);
        }

        return $next($request);
    }
}