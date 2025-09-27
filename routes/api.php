<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubscriptionController;

Route::prefix('subscriptions')->group(function () {
    Route::get('{id}/amount', [SubscriptionController::class, 'amount']);
    Route::post('{id}/transition', [SubscriptionController::class, 'transition']);
});