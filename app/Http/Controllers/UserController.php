<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use Log;
use Nette\Utils\Strings;

class UserController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['clubs', 'events'])->get();
        return $users;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $email = $request['email'];
        if (User::where('email', $email)->first()) {
            return response()->json([
                'message' => 'User already exists',
            ], 422);
        }
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => 'student',
            'image' => 'images/default.jpg',
            'department' => $request['department'],
        ]);

        return response()->json(['user' => $user, 'message' => 'User created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        Log::info('Attempting to show user: ' . $user->id);
        return response()->json($user->load(['clubs', 'events']), 200);
    }

    /**
     * Update the specified resource in storage.
     */
   



    public function updatePassword(UpdatePasswordRequest $request)
    {
        \Illuminate\Support\Facades\Log::info('updatePassword method reached!');

        $user = User::where('id', $request->id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json(['message' => 'Current password does not match'], 400);
        }
        
        $user->update([ 
            'password' => Hash::make($request->newPassword),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}
