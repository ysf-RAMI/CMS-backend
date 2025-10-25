<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with(["users"])->orderBy("created_at", "desc")->get();
        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "club_id" => "required",
            "title" => "required|string|max:255",
            "description" => "required|string|max:255",
            'date' => 'required|date',
            "location" => "required|string|max:255",
            "image" => "required|file|image|mimes:jpeg,png,jpg,gif|max:2048",
            "max_participants" => "required|integer|min:0",
            "created_by" => "required|exists:user,id",
        ]);

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::with(["users"])->findOrFail($id);
        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['message'=>'Event not found'], 404);
        }
        $event->delete();
        return response()->json(['message'=>'Event deleted successfully'], 204);
    }

    public function register(Request $request, Event $event, $userId)
    {
        if ($event->registrations()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Already registered'], 400);
        }

        $event->registrations()->attach($userId, ['status' => 'pending']);
        return response()->json(['message' => 'Registration pending approval']);
    }

    public function approveRegistration(Request $request, Event $event, $userId)
    {
        $event->registrations()->updateExistingPivot($userId, ['status' => 'approved']);
        return response()->json(['message' => 'Registration approved']);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}