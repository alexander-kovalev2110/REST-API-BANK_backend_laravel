<?php

namespace App\Application\Transaction\Query;

use App\Application\Transaction\Service\TransactionQueryInterface;
use App\Application\Transaction\DTO\TransactionListView;

final class GetTransactionHandler
{
    public function __construct(
        private TransactionQueryInterface $queryService
    ) {}

    public function __invoke(GetTransactionQuery $query): TransactionListView
    {
        return $this->queryService->getTransaction($query->customer, $query->transactionId);
    }
}