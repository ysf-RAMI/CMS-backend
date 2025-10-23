<?php

use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;




Route::apiResource('club', ClubController::class);
Route::apiResource('event', EventController::class);




// User Controller Routes
Route::put('/user/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword']);
Route::get('/user', [App\Http\Controllers\UserController::class, 'index']);
Route::get('/user/{user}', [App\Http\Controllers\UserController::class, 'show']);
Route::put('/user/{user}', [App\Http\Controllers\UserController::class, 'update']);
Route::post('/user/{user}', [App\Http\Controllers\UserController::class, 'update']); // For form-data with file uploads
Route::delete('/user/{user}', [App\Http\Controllers\UserController::class, 'destroy']); // For form-data with file uploads



// Event Registration Controller Routes
Route::apiResource('event-registration', EventRegistrationController::class);
Route::post('/events/{event}/register/{userId}', [EventController::class, 'register']);
Route::post('/events/{event}/approve/{userId}', [EventController::class, 'approveRegistration']);