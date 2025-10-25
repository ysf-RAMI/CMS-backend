<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use Illuminate\Support\Facades\Route;





// Club controller 
Route::apiResource('club', ClubController::class);
Route::get('/club', [ClubController::class, 'index']);
Route::post('/club', [ClubController::class, 'store']);
Route::get('/club/{club}', [ClubController::class, 'show']);
Route::put('/club/{club}', [ClubController::class, 'update']);
Route::delete('/club/{club}', [ClubController::class, 'destroy']);



// Event controller 
Route::apiResource('event', EventController::class);
Route::get('/event', [EventController::class, 'index']);
Route::post('/event', [EventController::class, 'store']);
Route::get('/event/{event}', [EventController::class, 'show']);
Route::put('/event/{event}', [EventController::class, 'update']);
Route::delete('/event/{event}', [EventController::class, 'destroy']);






// User Controller Routes
Route::put('/user/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword']);
Route::get('/user/{user}', [App\Http\Controllers\UserController::class, 'show']);
Route::put('/user/{user}', [App\Http\Controllers\UserController::class, 'update']);
Route::post('/user/{user}', [App\Http\Controllers\UserController::class, 'store']); // For form-data with file uploads
Route::delete('/user/{user}', [App\Http\Controllers\UserController::class, 'destroy']); // For form-data with file uploads



// Event Registration Controller Routes
Route::apiResource('event-registration', EventRegistrationController::class);
Route::post('/events/{event}/register/{userId}', [EventController::class, 'register']);
Route::post('/events/{event}/approve/{userId}', [EventController::class, 'approveRegistration']);



// Auth Controller Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh');
    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});