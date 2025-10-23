<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with(["club", "users"])->orderBy("created_at", "desc")->get();
        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "club_id" => "required|exists:club,id",
            "title" => "required|string|max:255",
            "description" => "required|string|max:255",
            "date" => "required",
            "location" => "required|string|max:255",
            "image" => "required|file|image|mimes:jpeg,png,jpg,gif|max:2048",
            "max_participants" => "required|integer|min:0",
            "created_by" => "required|exists:user,id",
        ]);

        $event = Event::create($request->all());
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
}