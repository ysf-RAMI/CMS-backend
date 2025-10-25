<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Club::with(["users", "events"])->get();
      } 

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categorie' => 'required|string|max:255',
            'max_members' => 'required|integer',
            'created_by' => 'required|integer|exists:user,id',
        ]);

        $club = Club::create($validatedData);

        return response()->json($club, 201);
    }

    /**
     * Display the specified resource.
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
        $club = Club::find($id);    
        if (!$club) {
            return response()->json(['message'=>'Club not found'], 404);
        }
        $club->delete();
        return response()->json(['message'=>'Club deleted successfully'], 204);
    }
}
