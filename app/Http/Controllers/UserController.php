<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Log;

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
                'message' => 'Email already exists',
            ], 422);
        }
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => 'student',
            'image' => $request['image'],
            'department' => $request['department'],
        ]);

        return response()->json(['user' => $user, 'message' => 'User created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json($user->load(['clubs', 'events']), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
       

        if (isset($request['password'])) {
            $request['password'] = Hash::make($request['password']);
        }

        $user->update($request->all()); 

        return response()->json($user, 200);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        Log:info('updatePassword method reached!');

        $user = User::where('id', $request->id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $password = Hash::make($user['password']);
        if (!Hash::check($request->currentPassword, $password)) {
            return response()->json(['message' => 'Current password does not match'], 400);
        }
        $user->update([ 
            'password' => Hash::make($request->newPassword),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}
