<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Get all events",
     *     tags={"Events"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Event"))
     *     )
     * )
     */
    public function index()
    {
        $events = Event::with(["users"])->orderBy("created_at", "desc")->get();
        return response()->json($events);
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Create a new event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"club_id","title","description","date","location","max_participants"},
     *             @OA\Property(property="club_id", type="string", format="uuid", example="some-club-uuid"),
     *             @OA\Property(property="title", type="string", example="Annual Gala"),
     *             @OA\Property(property="description", type="string", example="Our annual fundraising gala"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="location", type="string", example="Grand Ballroom"),
     *             @OA\Property(property="image", type="string", format="binary", description="Event image (optional)"),
     *             @OA\Property(property="max_participants", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
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
            "club_id" => "required",
            "title" => "required|string|max:255",
            "description" => "required|string|max:255",
            'date' => 'required|date',
            "location" => "required|string|max:255",
            "image" => "nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048",
            "max_participants" => "required|integer|min:0",
        ]);

        

        $validatedData['created_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = Str::uuid() . '_' . time() . '.' . $extension;
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = 'images/' . $imageName;
        }
        $event = Event::create($validatedData);
        return response()->json($event, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Get a specific event",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event to retrieve",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $event = Event::with(["users"])->findOrFail($id);
        return response()->json($event);
    }

    /**
     * @OA\Post(
     *     path="/api/events/{id}",
     *     summary="Update an event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="club_id", type="string", format="uuid", example="some-club-uuid"),
     *             @OA\Property(property="title", type="string", example="Updated Annual Gala"),
     *             @OA\Property(property="description", type="string", example="An updated description for the annual fundraising gala"),
     *             @OA\Property(property="date", type="string", format="date", example="2026-01-01"),
     *             @OA\Property(property="location", type="string", example="New Grand Ballroom"),
     *             @OA\Property(property="image", type="string", format="binary", description="New event image (optional)"),
     *             @OA\Property(property="max_participants", type="integer", example=120),
     *             @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
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
        $event = Event::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'location' => 'sometimes|string|max:255',
            'image' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:2048', // Added image validation
            'max_participants' => 'sometimes|integer|min:0',
            'status' => 'sometimes|string|in:pending,approved,rejected', // Added status validation
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($event->image && file_exists(public_path($event->image))) {
                unlink(public_path($event->image));
            }

            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = Str::uuid() . '_' . time() . '.' . $extension;
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = 'images/' . $imageName;
        }

        $event->update($validatedData);
        return response()->json($event);
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Delete an event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
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
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        $event->delete();
        return response()->json( 'Event deleted successfully', 200);
    }



    

    /**
     * @OA\Post(
     *     path="/api/events/{id}/accept",
     *     summary="Accept an event",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event to accept",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event approved successfully"),
     *             @OA\Property(property="event", ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
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
    public function accepteEvent($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->status = 'approved';
        $event->save();

        return response()->json(['message' => 'Event approved successfully', 'event' => $event], 200);
    }

}