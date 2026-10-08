<?php

namespace App\Infrastructure\Security;

use App\Domain\Customer\Customer;
use App\Application\Customer\Service\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(Customer $customer, string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function isValid(Customer $customer, string $plainPassword): bool
    {
        return Hash::check($plainPassword, $customer->getPassword());
    }
}