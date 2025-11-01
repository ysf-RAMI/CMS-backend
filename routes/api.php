<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use Illuminate\Support\Facades\Route;


// Auth Controller Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'store']);
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh');
    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


// Auth Middleware
Route::middleware('auth:api')->group(function () {

    // Admin api
    Route::middleware('role:admin')->group(function () {
        Route::post('/clubs', [ClubController::class, 'store']);
        Route::put('/clubs/{id}', [ClubController::class, 'update']);
        Route::delete('/clubs/{club}', [ClubController::class, 'destroy']);
        Route::put('/event/status/{id}', [EventController::class, 'accepteEvent']);
    });

    // AdminMember api 
    Route::middleware('role:admin-member')->group(function () {
        Route::post('/clubs/approve-student', [ClubController::class, 'approveStudent']);
        Route::post('/event', [EventController::class, 'store']);
        Route::put('/event/{event}', [EventController::class, 'update']);
        Route::delete('/event/{event}', [EventController::class, 'destroy']);
        Route::post('/events/{event}/approve/{userId}', [EventController::class, 'approveRegistration']);
        Route::get('/clubs/event', function () {
            return response()->json(['message' => 'Club Event Access Granted'], 200);
        });
    });

    // Member api 
    Route::middleware('role:member')->group(function () {

    });

    // Student , Member , AdminMemeber api
    Route::middleware('role:student,member,admin-member')->group(function () {
        Route::post('/event/register', [EventRegistrationController::class, 'store']);
    });

    // All Users Type api
    Route::middleware('role:student,member,admin-member,admin')->group(function () {
        Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update']);
        Route::put('/users/{id}/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword']);
    });

});

Route::group(['middleware' => ['api']], function () {
    // Public Club controller 
    Route::get('/clubs/{club}', [ClubController::class, 'show']);
    Route::get('/clubs', [ClubController::class, 'index']);
    Route::post('/clubs/join', [ClubController::class, 'joinclub']);


    // Public Event Controller
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::get('/events', [EventController::class, 'index']);

    // Public User Controller Routes
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('/users/{user}', [App\Http\Controllers\UserController::class, 'show']);
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy']);
});




// Uniticated Controller
Route::get('/login', function () {
    return response()->json(['message' => 'Authentication Required'], 401);
})->name('login');

