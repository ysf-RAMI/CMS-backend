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
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","department"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret"),
     *             @OA\Property(property="role", type="string", enum={"admin", "admin-member", "member", "student"}, example="student"),
     *             @OA\Property(property="department", type="string", example="Computer Science")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User already exists")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to retrieve",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane.doe@example.com"),
     *             @OA\Property(property="department", type="string", example="Electrical Engineering"),
     *             @OA\Property(property="image", type="string", format="binary", description="User profile image (optional)"),
     *             @OA\Property(property="role", type="string", enum={"admin", "admin-member", "member", "student"}, example="member")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
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


    /**
     * @OA\Put(
     *     path="/api/users/{id}/password",
     *     summary="Update user password",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update password for",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"currentPassword","newPassword","newPassword_confirmation"},
     *             @OA\Property(property="currentPassword", type="string", format="password", example="secret"),
     *             @OA\Property(property="newPassword", type="string", format="password", example="new_secret"),
     *             @OA\Property(property="newPassword_confirmation", type="string", format="password", example="new_secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Current password does not match")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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


    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not founded")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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
