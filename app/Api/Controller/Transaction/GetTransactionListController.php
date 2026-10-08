<?php

namespace App\Api\Controller\Transaction;

use App\Application\Transaction\Query\GetTransactionListQuery;
use App\Infrastructure\Bus\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetTransactionListController extends Controller
{
    public function __invoke(
        Request $request,
        QueryBus $queryBus
    ): JsonResponse {
        $customer = $request->attributes->get('current_customer');

        $amount = $request->has('amount') ? (float) $request->query('amount') : null;
        $dateStr = $request->query('date');
        $date = $dateStr ? \DateTimeImmutable::createFromFormat('Y-m-d', $dateStr) ?: null : null;
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);

        $query = new GetTransactionListQuery(
            customer: $customer,
            amount: $amount,
            date: $date,
            page: max(1, $page),
            limit: max(1, $limit)
        );

        $result = $queryBus->ask($query);

        return response()->json($result, 200);
    }
}