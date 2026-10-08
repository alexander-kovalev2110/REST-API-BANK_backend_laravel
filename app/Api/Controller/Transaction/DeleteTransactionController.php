<?php

namespace App\Api\Controller\Transaction;

use App\Application\Transaction\Command\DeleteTransactionCommand;
use App\Infrastructure\Bus\CommandBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class DeleteTransactionController extends Controller
{
    public function __invoke(
        int $transactionId,
        Request $request,
        CommandBus $commandBus
    ): JsonResponse {
        $customer = $request->attributes->get('current_customer');

        $result = $commandBus->dispatch(
            new DeleteTransactionCommand($customer, $transactionId)
        );

        return response()->json($result, 200);
    }
}