<?php

namespace App\Api\Controller\Transaction;

use App\Application\Transaction\Command\CreateTransactionCommand;
use App\Infrastructure\Bus\CommandBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

final class CreateTransactionController extends Controller
{
    public function __invoke(
        Request $request,
        CommandBus $commandBus
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0',
        ], [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a numeric.',
            'amount.gt' => 'Amount must be greater than zero.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $customer = $request->attributes->get('current_customer');

        $result = $commandBus->dispatch(
            new CreateTransactionCommand($customer, (float) $request->input('amount'))
        );

        return response()->json($result, 201);
    }
}