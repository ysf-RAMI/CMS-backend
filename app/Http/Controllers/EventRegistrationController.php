<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEventRegistration;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EventRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return EventRegistration::with(['event', 'user'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddEventRegistration $request)
    {
        $validatedData = $request->validated();
        $eventId = $validatedData['event_id'];
        $userId = $validatedData['user_id'];
        $exist = EventRegistration::with(['event', 'user'])->where('event_id', $eventId)->where('user_id', $userId)->first();   
        if ($exist) {
            return response()->json(['message' => 'You already registered for this event'], 400);
        }
        $eventRegistration = EventRegistration::create([
            'event_id' => $validatedData['event_id'],
            'user_id' => $validatedData['user_id'],
            'registered_at' => now(),
            'status' => 'pending',
        ]);

        return response()->json($eventRegistration, 201);
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $eventRegistration = EventRegistration::find($id);

        if (!$eventRegistration) {
            return response()->json(['message' => 'Event Registration not found'], 404);
        }

        $eventRegistration->update($request->all());

        return response()->json($eventRegistration);
    }

    /**
     * Remove the specified resource from storage.
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
