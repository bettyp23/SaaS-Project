<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Todo\TodoController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\Tag\TagController;
use App\Http\Controllers\Subscription\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/auth/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/auth/login', [LoginController::class, 'login']);

Route::get('/auth/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/auth/register', [RegisterController::class, 'register']);

Route::post('/auth/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Tasks (All Tasks page)
    Route::get('/tasks', [TodoController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TodoController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TodoController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{todo}/edit', [TodoController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{todo}', [TodoController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{todo}', [TodoController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{todo}/toggle-status', [TodoController::class, 'toggleStatus'])->name('tasks.toggle-status');
    
    // Kanban Board
    Route::get('/kanban', [TodoController::class, 'kanban'])->name('kanban');
    Route::put('/tasks/{todo}/update-status', [TodoController::class, 'updateStatus'])->name('tasks.update-status');
    
    // Calendar
    Route::get('/calendar', [TodoController::class, 'calendar'])->name('calendar');
    
    // Categories (Tags)
    Route::get('/categories', [TagController::class, 'index'])->name('categories.index');
    Route::post('/categories', [TagController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{tag}', [TagController::class, 'destroy'])->name('categories.destroy');
    
    // Archive
    Route::get('/archive', [TodoController::class, 'archive'])->name('archive');
    Route::post('/tasks/{todo}/restore', [TodoController::class, 'restore'])->name('tasks.restore');
    
    // Settings
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    
    // Teams
    Route::resource('teams', TeamController::class);
    
    // Tags (API endpoint)
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    
    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
});
