<?php

use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;




Route::apiResource('club', ClubController::class);
Route::apiResource('event', EventController::class);




// User Controller Routes
Route::put('/user/updatePassword', [UserController::class, 'updatePassword']);
Route::apiResource('user', UserController::class);


// Event Registration Controller Routes
Route::apiResource('event-registration', EventRegistrationController::class);



