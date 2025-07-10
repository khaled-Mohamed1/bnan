<?php

use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['SetLocal']], function () {
    // Include Auth Routes
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);

    Route::post('/users/{user}/assign-subscription', [UserController::class, 'assignSubscription']);
    Route::get('/users/{user}/current-subscription', [UserController::class, 'showCurrentSubscription']);
});


