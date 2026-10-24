<?php

namespace App\Api\Controller\Customer;

use App\Application\Customer\Command\LoginCustomerCommand;
use App\Application\Customer\DTO\AuthView;
use App\Application\Customer\Service\TokenServiceInterface;
use App\Infrastructure\Bus\CommandBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

final class LoginController extends Controller
{
    public function __invoke(
        Request $request,
        CommandBus $commandBus,
        TokenServiceInterface $tokenService
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ], [
            'name.required' => 'Name is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $customer = $commandBus->dispatch(
            new LoginCustomerCommand(
                $request->input('name'),
                $request->input('password')
            )
        );

        $token = $tokenService->createToken($customer);

        return response()->json(new AuthView($token), 200);
    }
}