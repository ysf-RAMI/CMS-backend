<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;


// Auth Controller Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register-public', [App\Http\Controllers\UserController::class, 'store']); 
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh');
    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


// Auth Middleware
Route::middleware('auth:api')->group(function () {

    // Admin api
    Route::middleware('role:admin')->group(function () {
        Route::post('/club', [ClubController::class, 'store']);
        Route::put('/club/{id}', [ClubController::class, 'update']);
        Route::delete('/club/{club}', [ClubController::class, 'destroy']);
        Route::put('/event/status/{id}', [EventController::class, 'accepteEvent']);
    });

    // AdminMember api 
    Route::middleware('role:admin-member')->group(function () {
        Route::post('/event', [EventController::class, 'store']);
        Route::put('/event/{event}', [EventController::class, 'update']);
        Route::delete('/event/{event}', [EventController::class, 'destroy']);
        Route::post('/events/{event}/approve/{userId}', [EventController::class, 'approveRegistration']);
        Route::get('/club/event', function () {
            return response()->json(['message' => 'Club Event Access Granted'], 200);
        });
    });

    // Member api 
    Route::middleware('role:member')->group(function () {
        Route::get('/event', [EventController::class, 'index']);
    });

    // Student , Member , AdminMemeber api
    Route::middleware('role:student,member,admin-member')->group(function () {
        Route::post('/events/{event}/register/{userId}', [EventController::class, 'register']);
    });

    // All Users Type api
    Route::middleware('role:student,member,admin-member,admin')->group(function () {
        Route::put('/user/{user}', [App\Http\Controllers\UserController::class, 'update']);
        Route::put('/user/{id}/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword']);
    });

});

// Public Club controller 
Route::get('/club/{club}', [ClubController::class, 'show']);
Route::get('/club', [ClubController::class, 'index']);


// Public Event Controller
Route::get('/event/{event}', [EventController::class, 'show']);
Route::get('/event', [EventController::class, 'index']);

// Public User Controller Routes
Route::get('/user', [App\Http\Controllers\UserController::class, 'index']);
Route::get('/user/{user}', [App\Http\Controllers\UserController::class, 'show']);
Route::delete('/user/{user}', [App\Http\Controllers\UserController::class, 'destroy']); // For form-data with file uploads






// Uniticated Controller
Route::get('/login', function () {
    return response()->json(['message' => 'Authentication Required'], 401);
})->name('login');
