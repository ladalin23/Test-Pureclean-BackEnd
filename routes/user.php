<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Auth\UserAuthController;
use App\Http\Controllers\Api\v1\User\FeedbackController;
use App\Http\Controllers\Api\v1\User\NewsController;
use App\Http\Controllers\Api\v1\User\TelegramAuthController;
use App\Http\Controllers\Api\v1\User\TelegramWebhookController;
use App\Http\Controllers\Api\v1\User\UserController;
use App\Http\Controllers\Api\v1\User\LoyaltyController;
use App\Http\Controllers\Api\v1\User\RewardController;
use App\Http\Controllers\Api\v1\User\BannersController;
use App\Http\Controllers\Api\v1\User\GuideController;
use App\Models\DeviceToken;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/verify-otp', [UserAuthController::class, 'verifyOtp']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('auth/telegram/verify', [TelegramAuthController::class, 'verify']);

Route::group(['middleware' => 'auth:api-user', 'prefix' => 'auth/v1'], function ($router) {
    Route::post('/refresh-token', [UserAuthController::class, 'refreshToken']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
    
    Route::get('/banners', [BannersController::class, 'index']);

    Route::get('/guides', [GuideController::class, 'getAllActiveGuides']);
    // Feedback 
    Route::post('/feedbacks', [FeedbackController::class, 'store']);

    // News api routes
    Route::get('/news', [NewsController::class, 'getAllActiveNews']);
    Route::get('/news/{id}', [NewsController::class, 'show']);

    // User 
    Route::get('/users', [UserController::class, 'show']);
    Route::put('/users', [UserController::class, 'update']);

    // Loyalty Card
    Route::get('/loyalty-cards', [LoyaltyController::class, 'show']);

    // Rewards 
    Route::get('/rewards', [RewardController::class, 'index']);

    Route::post('/fcm/register',   [FCMTokenController::class, 'register']);
    Route::post('/fcm/unregister', [FCMTokenController::class, 'unregister']);



});
