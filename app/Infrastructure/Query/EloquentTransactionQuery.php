<?php

namespace App\Infrastructure\Query;

use App\Domain\Customer\Customer;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Application\Transaction\Service\TransactionQueryInterface;
use App\Application\Transaction\DTO\TransactionView;
use App\Application\Transaction\DTO\TransactionListView;

final class EloquentTransactionQuery implements TransactionQueryInterface
{
    public function getTransaction(Customer $customer, int $transactionId): TransactionListView
    {
        $transaction = Transaction::where('id', $transactionId)
            ->where('customer_id', $customer->getId())
            ->first();

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        return new TransactionListView(
            transactions: [
                new TransactionView(
                    $transaction->getId(),
                    $transaction->getAmount(),
                    $transaction->getDate()->format('Y-m-d'),
                    $transaction->getCustomer()->getId()
                )
            ],
            total: 1
        );
    }
}