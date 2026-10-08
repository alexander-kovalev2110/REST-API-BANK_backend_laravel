<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Customer\CustomerRepositoryInterface;
use App\Infrastructure\Repository\EloquentCustomerRepository;
use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Infrastructure\Repository\EloquentTransactionRepository;
use App\Application\Customer\Service\PasswordHasherInterface;
use App\Infrastructure\Security\LaravelPasswordHasher;
use App\Application\Customer\Service\TokenServiceInterface;
use App\Infrastructure\Security\JWTTokenService;
use App\Application\Transaction\Service\TransactionQueryInterface;
use App\Infrastructure\Query\EloquentTransactionQuery;
use App\Application\Transaction\Service\TransactionListQueryInterface;
use App\Infrastructure\Query\EloquentTransactionListQuery;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenServiceInterface::class, JWTTokenService::class);
        $this->app->bind(TransactionQueryInterface::class, EloquentTransactionQuery::class);
        $this->app->bind(TransactionListQueryInterface::class, EloquentTransactionListQuery::class);
    }

    public function boot(): void
    {
    }
}