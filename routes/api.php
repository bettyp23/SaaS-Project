<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\TodoListController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('resend-verification', [AuthController::class, 'resendVerification']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('enable-2fa', [AuthController::class, 'enable2FA']);
        Route::post('disable-2fa', [AuthController::class, 'disable2FA']);
        Route::post('verify-2fa', [AuthController::class, 'verify2FA']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
    });

    // User routes
    Route::prefix('user')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::post('avatar', [UserController::class, 'uploadAvatar']);
        Route::delete('avatar', [UserController::class, 'deleteAvatar']);
        Route::get('preferences', [UserController::class, 'getPreferences']);
        Route::put('preferences', [UserController::class, 'updatePreferences']);
        Route::get('statistics', [UserController::class, 'statistics']);
        Route::delete('account', [UserController::class, 'deleteAccount']);
        Route::get('export', [UserController::class, 'exportData']);
    });

    // Todo routes
    Route::prefix('todos')->group(function () {
        Route::get('/', [TodoController::class, 'index']);
        Route::post('/', [TodoController::class, 'store']);
        Route::get('statistics', [TodoController::class, 'statistics']);
        Route::post('bulk', [TodoController::class, 'bulk']);
        Route::get('{todo}', [TodoController::class, 'show']);
        Route::put('{todo}', [TodoController::class, 'update']);
        Route::delete('{todo}', [TodoController::class, 'destroy']);
        Route::post('{todo}/complete', [TodoController::class, 'complete']);
        Route::post('{todo}/pending', [TodoController::class, 'pending']);
    });

    // Todo List routes
    Route::prefix('lists')->group(function () {
        Route::get('/', [TodoListController::class, 'index']);
        Route::post('/', [TodoListController::class, 'store']);
        Route::get('{list}', [TodoListController::class, 'show']);
        Route::put('{list}', [TodoListController::class, 'update']);
        Route::delete('{list}', [TodoListController::class, 'destroy']);
        Route::get('{list}/todos', [TodoListController::class, 'todos']);
        Route::get('{list}/statistics', [TodoListController::class, 'statistics']);
        Route::post('{list}/reorder', [TodoListController::class, 'reorder']);
    });

    // Tag routes
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index']);
        Route::post('/', [TagController::class, 'store']);
        Route::get('{tag}', [TagController::class, 'show']);
        Route::put('{tag}', [TagController::class, 'update']);
        Route::delete('{tag}', [TagController::class, 'destroy']);
        Route::get('{tag}/todos', [TagController::class, 'todos']);
        Route::get('{tag}/statistics', [TagController::class, 'statistics']);
    });

    // Team routes
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index']);
        Route::post('/', [TeamController::class, 'store']);
        Route::get('{team}', [TeamController::class, 'show']);
        Route::put('{team}', [TeamController::class, 'update']);
        Route::delete('{team}', [TeamController::class, 'destroy']);
        Route::post('{team}/members', [TeamController::class, 'addMember']);
        Route::put('{team}/members/{user}', [TeamController::class, 'updateMember']);
        Route::delete('{team}/members/{user}', [TeamController::class, 'removeMember']);
        Route::get('{team}/members', [TeamController::class, 'members']);
        Route::get('{team}/statistics', [TeamController::class, 'statistics']);
        Route::post('{team}/invite', [TeamController::class, 'invite']);
        Route::post('{team}/join', [TeamController::class, 'join']);
    });

    // Subscription routes
    Route::prefix('subscription')->group(function () {
        Route::get('plans', [SubscriptionController::class, 'plans']);
        Route::get('current', [SubscriptionController::class, 'current']);
        Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('cancel', [SubscriptionController::class, 'cancel']);
        Route::post('reactivate', [SubscriptionController::class, 'reactivate']);
        Route::get('invoices', [SubscriptionController::class, 'invoices']);
        Route::get('usage', [SubscriptionController::class, 'usage']);
    });

    // Webhook routes (no auth required for webhooks)
    Route::post('webhooks/stripe', [SubscriptionController::class, 'stripeWebhook']);
});

// Health check route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
    ]);
});

// API documentation route
Route::get('docs', function () {
    return response()->json([
        'message' => 'Todo Tracker SaaS API',
        'version' => '1.0.0',
        'endpoints' => [
            'auth' => [
                'POST /api/auth/register' => 'Register a new user',
                'POST /api/auth/login' => 'Login user',
                'POST /api/auth/logout' => 'Logout user',
                'GET /api/auth/me' => 'Get current user',
            ],
            'todos' => [
                'GET /api/todos' => 'Get user todos',
                'POST /api/todos' => 'Create todo',
                'GET /api/todos/{id}' => 'Get specific todo',
                'PUT /api/todos/{id}' => 'Update todo',
                'DELETE /api/todos/{id}' => 'Delete todo',
            ],
            'lists' => [
                'GET /api/lists' => 'Get user lists',
                'POST /api/lists' => 'Create list',
                'GET /api/lists/{id}' => 'Get specific list',
                'PUT /api/lists/{id}' => 'Update list',
                'DELETE /api/lists/{id}' => 'Delete list',
            ],
        ],
    ]);
});
