<?php

namespace App\Api\Controller\Transaction;

use App\Application\Transaction\Query\GetTransactionQuery;
use App\Infrastructure\Bus\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class GetTransactionController extends Controller
{
    public function __invoke(
        int $transactionId,
        Request $request,
        QueryBus $queryBus
    ): JsonResponse {
        $customer = $request->attributes->get('current_customer');

        $result = $queryBus->ask(
            new GetTransactionQuery($customer, $transactionId)
        );

        return response()->json($result, 200);
    }
}