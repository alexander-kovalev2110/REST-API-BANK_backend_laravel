<?php

namespace Tests\Feature;

use App\Domain\Customer\Customer;
use App\Domain\Customer\Exception\CustomerAlreadyExistsException;
use App\Domain\Customer\Exception\CustomerNotFoundException;
use App\Domain\Customer\Exception\InvalidCredentialsException;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Infrastructure\Security\JWTTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function testHandlesValidationErrorReturnsSingleErrorField(): void
    {
        $response = $this->postJson('/customers/register', []);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Name is required.',
        ]);
        $this->assertArrayNotHasKey('errors', $response->json());
        $this->assertArrayNotHasKey('message', $response->json());
    }

    public function testHandlesLoginValidationErrorReturnsSingleErrorField(): void
    {
        $response = $this->postJson('/customers/login', []);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Name is required.',
        ]);
    }

    public function testHandlesTransactionValidationErrorReturnsSingleErrorField(): void
    {
        $customer = new Customer();
        $customer->setName('testuser');
        $customer->setPassword('hash123');
        $customer->save();

        $tokenService = new JWTTokenService();
        $token = $tokenService->createToken($customer);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/transactions', ['amount' => -10]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Amount must be greater than zero.',
        ]);
    }

    public function testHandlesMissingJwtToken(): void
    {
        $response = $this->postJson('/transactions', ['amount' => 100]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'JWT Token not found',
        ]);
    }

    public function testHandlesInvalidJwtToken(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid.token.value')
            ->postJson('/transactions', ['amount' => 100]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Invalid or expired JWT Token',
        ]);
    }

    public function testHandlesHttpNotFoundException(): void
    {
        $response = $this->getJson('/non-existent-route-for-testing');

        $response->assertStatus(404);
        $this->assertArrayHasKey('error', $response->json());
    }

    public function testHandlesDomainExceptionCustomerAlreadyExists(): void
    {
        Route::get('/test-domain-exception', function () {
            throw new CustomerAlreadyExistsException();
        });

        $response = $this->getJson('/test-domain-exception');

        $response->assertStatus(409);
        $response->assertExactJson([
            'error' => 'Customer already exists.',
        ]);
    }

    public function testHandlesDomainExceptionCustomerNotFound(): void
    {
        Route::get('/test-customer-not-found', function () {
            throw new CustomerNotFoundException();
        });

        $response = $this->getJson('/test-customer-not-found');

        $response->assertStatus(404);
        $response->assertExactJson([
            'error' => 'Customer not found.',
        ]);
    }

    public function testHandlesDomainExceptionInvalidCredentials(): void
    {
        Route::get('/test-invalid-credentials', function () {
            throw new InvalidCredentialsException();
        });

        $response = $this->getJson('/test-invalid-credentials');

        $response->assertStatus(401);
        $response->assertExactJson([
            'error' => 'Invalid credentials.',
        ]);
    }

    public function testHandlesDomainExceptionTransactionNotFound(): void
    {
        Route::get('/test-transaction-not-found', function () {
            throw new TransactionNotFoundException();
        });

        $response = $this->getJson('/test-transaction-not-found');

        $response->assertStatus(404);
        $response->assertExactJson([
            'error' => 'Transaction not found.',
        ]);
    }

    public function testHandlesGenericExceptionAs500(): void
    {
        Route::get('/test-generic-500', function () {
            throw new \RuntimeException('Database connection lost');
        });

        $response = $this->getJson('/test-generic-500');

        $response->assertStatus(500);
        $response->assertExactJson([
            'error' => 'Internal Server Error',
        ]);
    }

    public function testTokenContainsUsernameFieldInPayload(): void
    {
        $customer = new Customer();
        $customer->setName('alex_test');
        $customer->setPassword('hash123');
        $customer->save();

        $tokenService = new JWTTokenService();
        $token = $tokenService->createToken($customer);

        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(JWTTokenService::getSecret(), 'HS256'));
        $this->assertEquals('alex_test', $decoded->username);
    }
}
