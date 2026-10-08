<?php

namespace App\Infrastructure\Repository;

use App\Domain\Customer\Customer;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;

final class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void
    {
        $transaction->save();
    }

    public function remove(Transaction $transaction): void
    {
        $transaction->delete();
    }

    public function findByIdAndCustomer(int $id, Customer $customer): ?Transaction
    {
        return Transaction::where('id', $id)
            ->where('customer_id', $customer->getId())
            ->first();
    }
}