<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StyleController;

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/styles', [StyleController::class, 'index']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User profile management
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    
    // User-specific data
    Route::get('/user/groups', [UserController::class, 'getUserGroups']);
    Route::get('/user/events', [UserController::class, 'getUserEvents']);
    Route::put('/user/events/{id}/status', [UserController::class, 'updateEventStatus']);
    
    // General events (admin/public events)
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
});
