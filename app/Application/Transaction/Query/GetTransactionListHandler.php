<?php

namespace App\Application\Transaction\Query;

use App\Application\Transaction\Service\TransactionListQueryInterface;
use App\Application\Transaction\DTO\TransactionListView;

final class GetTransactionListHandler
{
    public function __construct(
        private TransactionListQueryInterface $queryService
    ) {}

    public function __invoke(GetTransactionListQuery $query): TransactionListView
    {
        return $this->queryService->getList($query);
    }
}