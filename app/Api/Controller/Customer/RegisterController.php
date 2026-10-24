<?php

namespace App\Api\Controller\Customer;

use App\Application\Customer\Command\RegisterCustomerCommand;
use App\Application\Customer\DTO\AuthView;
use App\Application\Customer\Service\TokenServiceInterface;
use App\Infrastructure\Bus\CommandBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

final class RegisterController extends Controller
{
    public function __invoke(
        Request $request,
        CommandBus $commandBus,
        TokenServiceInterface $tokenService
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'password' => 'required|string|min:3|max:255',
        ], [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 3 characters long.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 3 characters long.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $customer = $commandBus->dispatch(
            new RegisterCustomerCommand(
                $request->input('name'),
                $request->input('password')
            )
        );

        $token = $tokenService->createToken($customer);

        return response()->json(new AuthView($token), 201);
    }
}