<?php

namespace App\Infrastructure\Query;

use App\Domain\Transaction\Transaction;
use App\Application\Transaction\Query\GetTransactionListQuery;
use App\Application\Transaction\Service\TransactionListQueryInterface;
use App\Application\Transaction\DTO\TransactionView;
use App\Application\Transaction\DTO\TransactionListView;

final class EloquentTransactionListQuery implements TransactionListQueryInterface
{
    public function getList(GetTransactionListQuery $query): TransactionListView
    {
        $builder = Transaction::where('customer_id', $query->customer->getId());

        if ($query->amount !== null) {
            $builder->where('amount', $query->amount);
        }

        if ($query->date !== null) {
            $start = $query->date->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $end   = $query->date->setTime(23, 59, 59)->format('Y-m-d H:i:s');
            $builder->whereBetween('date', [$start, $end]);
        }

        $total = $builder->count();

        $offset = ($query->page - 1) * $query->limit;
        $transactions = $builder->orderBy('date', 'asc')
            ->offset($offset)
            ->limit($query->limit)
            ->get();

        $views = $transactions->map(function (Transaction $t) {
            return new TransactionView(
                $t->getId(),
                $t->getAmount(),
                $t->getDate()->format('Y-m-d'),
                $t->getCustomer()->getId()
            );
        })->all();

        return new TransactionListView($views, $total);
    }
}