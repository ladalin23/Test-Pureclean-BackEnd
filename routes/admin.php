<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Auth\AdminAuthController;
use App\Http\Controllers\Api\v1\Admin\AdminController;
use App\Http\Controllers\Api\v1\Admin\BranchController;
use App\Http\Controllers\Api\v1\Admin\ServiceController;
use App\Http\Controllers\Api\v1\Admin\UploadController;
use App\Http\Controllers\Api\v1\Admin\ProductController;
use App\Http\Controllers\Api\v1\Admin\SettingController;
use App\Http\Controllers\Api\v1\Admin\PurchasedController;
use App\Http\Controllers\Api\v1\Admin\LoyaltyCardController;
use App\Http\Controllers\Api\v1\Admin\RewardController;
use App\Http\Controllers\Api\v1\Admin\NewsController;
use App\Http\Controllers\Api\v1\Admin\FeedbackController;
use App\Http\Controllers\Api\v1\Admin\UserController;
use App\Http\Controllers\Api\v1\Admin\BannersController;
use App\Http\Controllers\Api\v1\Admin\DashboardController;
use App\Http\Controllers\Api\v1\Admin\GuideController;
use App\Http\Controllers\Api\v1\Admin\NotificationController;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Jobs\SendAdminMessageJob;

Route::post('/login', [ AdminAuthController::class, 'loginAdmin']);
// Route::apiResource('/admins', AdminController::class);
// Route::apiResource('/branches', BranchController::class);
Route::post('/otp', function (Request $request, BrevoService $brevo) {

    $otp = rand(100000, 999999);

    // Load your custom Blade HTML and pass OTP
    $html = view('emails.otp', ['otp' => $otp])->render();

    $brevo->sendSimpleEmail(
        $request->email,
        "Customer",
        "Your OTP Code",
        $html
    );

    return response()->json([
        'success' => true,
        'message' => 'OTP sent',
        'otp' => $otp // remove this when storing in DB
    ]);
});
//middleware routes
Route::group(['middleware' => 'auth:api', 'prefix' => 'auth/v1'], function ($router) {
    Route::post('/refresh-token', [AdminAuthController::class, 'refreshToken']);
    Route::post('/logout', [AdminAuthController::class, 'logoutAdmin']);

    Route::patch('/admins/{admin}/status/{status}', [AdminController::class, 'changeStatus']);
    Route::apiResource('/admins', AdminController::class);
    // Branch Api Resource Routes
    Route::get('/branches/active', [BranchController::class, 'getAllActiveBranches']);
    Route::patch('/branches/{branch}/status/{status}', [BranchController::class, 'changeStatus']);
    Route::apiResource('/branches', BranchController::class);

    // Service Api Resource Routes
    Route::get('/services/active', [ServiceController::class, 'getAllActiveServices']);
    // Route::get('/services', [ServiceController::class, 'show']);
    Route::patch('/services/{service}/status/{status}', [ServiceController::class, 'changeStatus']);
    Route::apiResource('/services', ServiceController::class);

    // Product Api Resource Routes
    Route::get('/products/active', [ProductController::class, 'getAllActiveProducts']);
    Route::patch('/products/{product}/status/{status}', [ProductController::class, 'changeStatus']);
    Route::apiResource('/products', ProductController::class);

    // Setting Api Resource Routes
    Route::get('/settings/active', [SettingController::class, 'getAllActiveSettings']);
    Route::patch('/settings/{setting}/status/{status}', [SettingController::class, 'changeStatus']);
    Route::apiResource('/settings', SettingController::class);

    // Purchased Api Resource Routes
    // Route::get('/purchased/active', [PurchasedController::class, 'getAllActivePurchased']);
    Route::patch('/purchased/{purchased}/status/{status}', [PurchasedController::class, 'changeStatus']);
    Route::apiResource('/purchased', PurchasedController::class);

    // Loyalty Card
    Route::get('/loyalty-cards/active', [LoyaltyCardController::class, 'getAllActiveLoyaltyCards']);
    Route::get('/loyalty-cards/user/{user}', [LoyaltyCardController::class, 'getLoyaltyCardsUser']);
    Route::apiResource('/loyalty-cards', LoyaltyCardController::class);

    // Reward Api Resource Routes
    // Route::get('/rewards/active', [RewardController::class, 'getAllActiveRewards']);
    // Route::patch('/rewards/{reward}/status/{status}', [RewardController::class, 'changeStatus']);
    Route::apiResource('/rewards', RewardController::class);

    // News Api Resource Routes
    Route::get('/news/active', [NewsController::class, 'getAllActiveNews']);
    Route::patch('/news/{news}/status/{status}', [NewsController::class, 'changeStatus']);
    Route::apiResource('/news', NewsController::class);

    // User Api Resource Routes
    Route::get('/users/active', [UserController::class, 'getAllActiveUsers']);
    Route::patch('/users/{user}/status/{status}', [UserController::class, 'changeStatus']);
    Route::apiResource('/users', UserController::class);

    // Banner API
    Route::get('/banners/active', [BannersController::class, 'getAllActiveBanners']);
    Route::patch('/banners/{banner}/status/{status}', [BannersController::class, 'changeStatus']);
    Route::apiResource('/banners', BannersController::class);

    // Guide API
    Route::get('/guides/active', [GuideController::class, 'getAllActiveGuides']);
    Route::patch('/guides/{guide}/status/{status}', [GuideController::class, 'changeStatus']);
    Route::apiResource('/guides', GuideController::class);

    // Feedback Api Resource Routes
    Route::apiResource('/feedbacks', FeedbackController::class);

    // File Upload & Delete
    Route::post('/uploads', [UploadController::class, 'store']);
    Route::delete('/uploads', [UploadController::class, 'destroy']);


    // ----------------------------------------
    // Dashboard Route
    // ----------------------------------------
    Route::get('/dashboard/top-users', [DashboardController::class, 'topUser']);
    Route::get('/dashboard/purchased-trends', [DashboardController::class, 'purchasedTrend']);
    Route::get('/dashboard/reward-claim-trends', [DashboardController::class, 'rewardClaimTrend']);
    Route::get('/dashboard/revenue-order', [DashboardController::class, 'revenueOrder']);

    // ===== Admin-only: create+send notification job ====
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::post('/notify', [NotificationController::class, 'sendToUsers']);  // user_ids[]
    Route::post('/notify/all',  [NotificationController::class, 'toAll']);   // topic

    Route::post('/telegram/send-to-users', [\App\Http\Controllers\Api\v1\Admin\TelegramMessageController::class, 'sendToUsers']);
    Route::post('/telegram/to-all', [\App\Http\Controllers\Api\v1\Admin\TelegramMessageController::class, 'toAll']);


});

