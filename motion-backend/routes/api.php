<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\InstructorController; 
use App\Http\Controllers\ContractController; 

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::get('/styles', [StyleController::class, 'index']);
Route::get('/instructors', [InstructorController::class, 'index']);
Route::get('/groups', [GroupController::class, 'index']);

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
    Route::delete('/user/groups/{id}', [UserController::class, 'leaveGroup']);
    
    // Event registration for all users
    Route::post('/events/{id}/register', [UserController::class, 'registerForEvent']);
    
    // Group joining - ONLY ContractController now
    Route::post('/groups/{id}/join', [ContractController::class, 'joinGroup']);
    
    // Browse available groups and events (detailed views)
    Route::get('/groups/{id}', [GroupController::class, 'show']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    
    // Admin event management
    Route::post('/events', [EventController::class, 'store']);

    // Admin routes (only for admins)
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/admin/groups', [AdminController::class, 'getGroups']);
        Route::post('/admin/groups', [AdminController::class, 'createGroup']);
        Route::delete('/admin/groups/{id}', [AdminController::class, 'deleteGroup']);
        Route::get('/admin/instructors', [AdminController::class, 'getInstructor']);
        Route::post('/admin/instructors', [AdminController::class, 'createInstructor']);
        Route::delete('/admin/instructors/{id}', [AdminController::class, 'deleteInstructor']);
    });

    // Admin profile routes (for logged-in admins)
    Route::get('/admin/profile', [AdminController::class, 'getProfile']);
    Route::put('/admin/profile', [AdminController::class, 'updateProfile']);
});
