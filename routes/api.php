<?php

use Illuminate\Support\Facades\Route;
use App\Api\Controller\Customer\RegisterController;
use App\Api\Controller\Customer\LoginController;
use App\Api\Controller\Transaction\CreateTransactionController;
use App\Api\Controller\Transaction\GetTransactionController;
use App\Api\Controller\Transaction\GetTransactionListController;
use App\Api\Controller\Transaction\UpdateTransactionController;
use App\Api\Controller\Transaction\DeleteTransactionController;
use App\Infrastructure\Middleware\JWTAuthMiddleware;

Route::post('/customers/register', RegisterController::class);
Route::post('/customers/login', LoginController::class);

Route::middleware([JWTAuthMiddleware::class])->group(function () {
    Route::post('/transactions', CreateTransactionController::class);
    Route::get('/transactions', GetTransactionListController::class);
    Route::get('/transactions/{transactionId}', GetTransactionController::class);
    Route::patch('/transactions/{transactionId}', UpdateTransactionController::class);
    Route::delete('/transactions/{transactionId}', DeleteTransactionController::class);
});