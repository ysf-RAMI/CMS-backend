<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
        // Cache clubs for 5 minutes
        return Cache::remember('clubs.all', 300, function () {
            return Club::with(['users', 'events'])
                ->withCount('users')
                ->orderBy('users_count', 'desc')
                ->get();
        });
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
        Log::info('ClubController@store: Request received.', [
            'Authorization_Header' => $request->header('Authorization'),
            'Auth_Check' => auth()->check(),
            'Auth_ID' => auth()->id(),
        ]);

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
            $validatedData['image'] = '/images/' . $imageName;
        } else {
            $validatedData['image'] = '/images/default_club_image.jpg'; // Set default image
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
            $validatedData['image'] = '/images/' . $imageName;
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
            return response()->json(['message' => 'Club not found'], 404);
        }
        $club->delete();
        return response()->json(['message' => 'Club deleted successfully'], 204);
    }

    /**
     * @OA\Post(
     *     path="/api/clubs/{club}/approve-student",
     *     summary="Approve or reject a student's club membership request",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="club",
     *         in="path",
     *         required=true,
     *         description="UUID of the club",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="some-club-uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "status"},
     *             @OA\Property(property="user_id", type="string", format="uuid", example="some-user-uuid"),
     *             @OA\Property(property="status", type="string", enum={"approved", "rejected"}, example="approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student status updated successfully (approved or rejected)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student status updated successfully")
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
    public function joinStudent(Request $request, Club $club)
    {
        $user_id = $request['user_id'];
        $status = $request['status'];
        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update user's role to 'member' if they are a student
        if ($user->role === 'student' && $status === 'approved') {
            $user->role = 'member';
            $user->save();
        }

        // Find or create ClubUser entry
        $clubUser = ClubUser::firstOrNew(
            ['user_id' => $user_id, 'club_id' => $club->id]
        );

        if ($status === 'rejected') {
            $clubUser->delete();
            return response()->json(['message' => 'Student request rejected and removed from club'], 200);
        }

        $clubUser->role = 'member';
        $clubUser->status = $status;
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
    public function joinclub($club_id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                \Log::error('Join Club: Unauthenticated user attempting to join club.', ['club_id' => $club_id]);
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $user_id = $user->id;

            \Log::info('Join Club: User attempting to join club.', ['user_id' => $user_id, 'club_id' => $club_id]);

            $foundUser = User::find($user_id);

            if (!$foundUser) {
                \Log::error('Join Club: User not found in database.', ['user_id' => $user_id, 'club_id' => $club_id]);
                return response()->json(['message' => 'User not found'], 404);
            }

            // Find or create ClubUser entry
            $clubUser = ClubUser::firstOrNew(
                ['user_id' => $user_id, 'club_id' => $club_id]
            );

            $clubUser->role = 'student'; // Set role to 'student' as they are requesting to join
            $clubUser->status = 'pending';
            $clubUser->joined_at = now();
            $clubUser->save();

            \Log::info('Join Club: User successfully requested to join club.', ['user_id' => $user_id, 'club_id' => $club_id]);
            return response()->json(['message' => 'Student added as pending to join the club'], 200);

        } catch (\Exception $e) {
            \Log::error('Join Club: An unexpected error occurred.', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'club_id' => $club_id,
                'user_id' => auth()->check() ? auth()->user()->id : 'unauthenticated'
            ]);
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }



    /**
     * @OA\Put(
     *     path="/api/clubs/{club}/admin",
     *     summary="Promote a member to admin",
     *     tags={"Clubs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="club",
     *         in="path",
     *         required=true,
     *         description="ID of the club",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="string", format="uuid", example="some-user-uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member promoted to admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Member promoted to admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Member not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Member not found")
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
    public function makeAdmin(Club $club, Request $request)
    {
        $clubUser = ClubUser::where('club_id', $club->id)->where('user_id', $request->input('user_id'))->first();
        if (!$clubUser) {
            return response()->json(['message' => 'Member not found'], 404);
        }
        $clubUser->role = 'admin-member';
        $clubUser->save();
        return response()->json(['message' => 'Member promoted to admin'], 200);
    }

}