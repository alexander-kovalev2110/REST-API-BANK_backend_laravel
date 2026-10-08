<?php

namespace App\Infrastructure\Repository;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;

final class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function save(Customer $customer): void
    {
        $customer->save();
    }

    public function findByName(string $name): ?Customer
    {
        return Customer::where('name', $name)->first();
    }
}