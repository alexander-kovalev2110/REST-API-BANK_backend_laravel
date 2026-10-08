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
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors[$key] = $messages[0];
            }
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $errors
            ], 400);
        }

        $customer = $request->attributes->get('current_customer');

        $result = $commandBus->dispatch(
            new CreateTransactionCommand($customer, (float) $request->input('amount'))
        );

        return response()->json($result, 201);
    }
}