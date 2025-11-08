<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEventRegistration;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EventRegistrationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events/register",
     *     summary="Get all event registrations",
     *     description="Get all event registrations. This is a public endpoint, no authentication required.",
     *     tags={"Event Registrations"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/EventRegistration"))
     *     )
     * )
     */
    public function index()
    {
        $registrations = EventRegistration::with(['event', 'user'])->get();
        return response()->json($registrations, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/events/register/{event_id}",
     *     summary="Register for an event",
     *     tags={"Event Registrations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         required=true,
     *         description="ID of the event to register for",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully registered for the event",
     *         @OA\JsonContent(ref="#/components/schemas/EventRegistration")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You already registered for this event, No places available, event is full")
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
    public function store($event_id)
    {
        $userId = auth()->user()->id;
        $event = Event::find($event_id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if the user is already registered for this event
        $existingRegistration = EventRegistration::where('event_id', $event_id)
            ->where('user_id', $userId)
            ->first();

        if ($existingRegistration) {
            return response()->json(['message' => 'You already registered for this event'], 400);
        }

        // Check if there are available spots
        $registeredParticipantsCount = EventRegistration::where('event_id', $event_id)->count();

        if ($registeredParticipantsCount >= $event->max_participants) {
            return response()->json(['message' => 'No places available, event is full'], 400);
        }

        $eventRegistration = EventRegistration::create([
            'event_id' => $event_id,
            'user_id' => $userId,
            'registered_at' => now(),
            'status' => 'pending',
        ]);

        return response()->json($eventRegistration, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/event-registrations/{id}",
     *     summary="Get a specific event registration",
     *     tags={"Event Registrations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event registration to retrieve",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EventRegistration")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event registration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event Registration not found")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $eventRegistration = EventRegistration::with(['event', 'user'])->find($id);

        if (!$eventRegistration) {
            return response()->json(['message' => 'Event Registration not found'], 404);
        }


        return response()->json($eventRegistration);
    }

    /**
     * @OA\Put(
     *     path="/api/event-registrations/{id}",
     *     summary="Update an event registration",
     *     tags={"Event Registrations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event registration to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="string", format="uuid", example="l6k7j8h9-0123-45gf-dsaq-wertyuioplkj"),
     *             @OA\Property(property="user_id", type="string", format="uuid", example="some-user-uuid"),
     *             @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event registration updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/EventRegistration")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event registration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event Registration not found")
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
        $eventRegistration = EventRegistration::find($id);

        if (!$eventRegistration) {
            return response()->json(['message' => 'Event Registration not found'], 404);
        }

        $eventRegistration->update($request->all());

        // Refresh the model to get updated data from database
        $eventRegistration->refresh();

        // Load relationships
        $eventRegistration->load(['event', 'user']);

        return response()->json($eventRegistration, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/event-registrations/{id}",
     *     summary="Delete an event registration",
     *     tags={"Event Registrations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event registration to delete",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event registration deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event Registration deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event registration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event Registration not found")
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
        $eventRegistration = EventRegistration::find($id);

        if (!$eventRegistration) {
            return response()->json(['message' => 'Event Registration not found'], 404);
        }

        $eventRegistration->delete();

        return response()->json(['message' => 'Event Registration deleted successfully']);
    }
}
