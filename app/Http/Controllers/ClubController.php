<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\UniqueConstraintViolationException;

class ClubController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/clubs",
     *     summary="Get all clubs",
     *     tags={"Clubs"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Club"))
     *     )
     * )
     */
    public function index()
    {
        return Club::with(["users", "events"])->get();
      } 

    /**
     * @OA\Post(
     *     path="/api/clubs",
     *     summary="Create a new club",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","categorie","max_members"},
     *             @OA\Property(property="name", type="string", example="Chess Club"),
     *             @OA\Property(property="description", type="string", example="A club for chess enthusiasts"),
     *             @OA\Property(property="image", type="string", format="binary", description="Club image (optional)"),
     *             @OA\Property(property="categorie", type="string", example="Academic"),
     *             @OA\Property(property="max_members", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Club created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Club")
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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'categorie' => 'required|string|max:255',
            'max_members' => 'required|integer',
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = \Illuminate\Support\Str::uuid() . '_' . time() . '.' . $extension;
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = 'images/' . $imageName;
        } else {
            $validatedData['image'] = 'images/default_club_image.jpg'; // Set default image
        }
      
        $validatedData['created_by'] = auth()->id();
        try {
            $club = Club::create($validatedData);
            return response()->json($club, 201);
        } catch (UniqueConstraintViolationException $e) {
            return response()->json(['message' => 'A club with this name already exists.'], 409);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/clubs/{id}",
     *     summary="Get a specific club",
     *     tags={"Clubs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the club to retrieve",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Club")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Club not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Club not found")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $club = Club::with(['users', 'events'])->find($id);

        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        return response()->json($club);
    }

    /**
     * @OA\Put(
     *     path="/api/clubs/{id}",
     *     summary="Update a club",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the club to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Chess Club"),
     *             @OA\Property(property="description", type="string", example="An updated description for the chess club"),
     *             @OA\Property(property="image", type="string", format="binary", description="New club image (optional)"),
     *             @OA\Property(property="categorie", type="string", example="Sports"),
     *             @OA\Property(property="max_members", type="integer", example=60)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Club updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Club updated successfully"),
     *             @OA\Property(property="club", ref="#/components/schemas/Club")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Club not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Club not found")
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
    public function update(Request $request, string $id)
    {
        $club = Club::find($id);

        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'categorie' => 'sometimes|string|max:255',
            'max_members' => 'sometimes|integer',
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = \Illuminate\Support\Str::uuid() . '_' . time() . '.' . $extension;
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = 'images/' . $imageName;
        }

        $club->update($validatedData);

        return response()->json([
            'message' => 'Club updated successfully',
            'club' => $club
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/clubs/{id}",
     *     summary="Delete a club",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the club to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Club deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Club deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Club not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Club not found")
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
    public function destroy(string $id)
    {
        $club = Club::find($id);    
        if (!$club) {
            return response()->json(['message'=>'Club not found'], 404);
        }
        $club->delete();
        return response()->json(['message'=>'Club deleted successfully'], 204);
    }

    /**
     * @OA\Post(
     *     path="/api/clubs/approve-student",
     *     summary="Approve a student to join a club",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","club_id"},
     *             @OA\Property(property="user_id", type="string", format="uuid", example="some-user-uuid"),
     *             @OA\Property(property="club_id", type="string", format="uuid", example="some-club-uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student approved and added to club as member",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student approved and added to club as member")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student not found")
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
    public function approveStudent(Request $request)
    {
        $user_id = $request['user_id'];
        $club_id = $request['club_id'];

        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update user's role to 'member' if they are a student
        if ($user->role === 'student') {
            $user->role = 'member';
            $user->save();
        }

        // Find or create ClubUser entry
        $clubUser = ClubUser::firstOrNew(
            ['user_id' => $user_id, 'club_id' => $club_id]
        );

        $clubUser->role = 'member';
        $clubUser->status = 'approved'; 
        $clubUser->joined_at = now(); 
        $clubUser->save();

        return response()->json(['message' => 'Student approved and added to club as member'], 200);
    }



    /**
     * @OA\Post(
     *     path="/api/clubs/join",
     *     summary="Request to join a club",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","club_id"},
     *             @OA\Property(property="user_id", type="string", format="uuid", example="some-user-uuid"),
     *             @OA\Property(property="club_id", type="string", format="uuid", example="some-club-uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student added as pending to join the club",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student added as pending to join the club")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student not found")
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
    public function joinclub(Request $request){
        $user_id = $request['user_id'];
        $club_id = $request['club_id'];

        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update user's role to 'member' if they are a student
        if ($user->role === 'student') {
            $user->role = 'member';
            $user->save();
        }

        // Find or create ClubUser entry
        $clubUser = ClubUser::firstOrNew(
            ['user_id' => $user_id, 'club_id' => $club_id]
        );

        $clubUser->role = 'member';
        $clubUser->status = 'pending';
        $clubUser->joined_at = now();
        $clubUser->save();

        return response()->json(['message' => 'Student added as pending to join the club'], 200);


    }


}
