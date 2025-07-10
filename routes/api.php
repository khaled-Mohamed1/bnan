<?php

use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['SetLocal']], function () {
    // Include Auth Routes
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);
    Route::patch('subscription-plans/{subscription_plan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus']);


    Route::post('/users/assign-subscription', [UserController::class, 'assignSubscription']);
    Route::get('/users/current-subscription/{user_subscription}', [UserController::class, 'showCurrentSubscription']);
    Route::get('/users/current-subscription', [UserController::class, 'indexCurrentSubscription']);


    
});


