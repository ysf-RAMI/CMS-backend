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
    public function show($id)
    {
        
        $user = User::where('id', $id)->first();
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        return response()->json($user->load(['clubs', 'events']), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Attempting to update user with ID: ' . $id);

        $user = User::where('id', $id)->first();
        
        if (!$user) {
            Log::info('User not found with ID: ' . $id);
            return response()->json(['message' => 'User not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'sometimes|string|max:255',
        ]);

        Log::info('Validated data: ' . json_encode($validatedData));

        $user->update($validatedData);

        Log::info('User updated successfully: ' . $user->id);

        return response()->json(['message' => 'User updated successfully', 'user' => $user->load(['clubs', 'events'])], 200);
    }



    public function updatePassword(UpdatePasswordRequest $request , $id)
    {
        \Illuminate\Support\Facades\Log::info('updatePassword method reached!');

        $user = User::where('id', $id)->first();

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
