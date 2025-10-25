<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Str;
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
        return response(
            $users,
            200
        );

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
            'role' => $request['role'] ?? 'student',
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

        return new UserResource($user->load(['clubs', 'events']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // 1. Find the user
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // 2. Validate the incoming data
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:user,email,' . $user->id,
            'department' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'sometimes|string|in:admin,admin-member,member,student',
        ]);

        // 3. If there's an image file, upload it
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = Str::uuid() . '_' . time() . '.' . $extension;
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = 'images/' . $imageName;
        }

        // 4. Update the user with the new data
        $user->update($validatedData);

        // 5. Refresh to get the updated data from database
        $user->refresh();

        // 6. Return success response with updated user
        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user->load(['clubs', 'events']))
        ], 200);
    }



    public function updatePassword(UpdatePasswordRequest $request, $id)
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


    public function destroy($id)
    {

        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not founded'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted'], 202);

    }

}
