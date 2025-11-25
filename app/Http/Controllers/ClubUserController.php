<?php

namespace App\Http\Controllers;

use App\Models\ClubUser;
use Illuminate\Http\Request;

class ClubUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

    public function getUserClubRequests(Request $request, $userId)
    {
        $status = $request->query('status', 'all');
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);

        $query = ClubUser::where('user_id', $userId)->with('club');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $clubUsers = $query->paginate($limit, ['*'], 'page', $page);

        $data = $clubUsers->map(function ($clubUser) {
            return [
                'id' => $clubUser->club->id,
                'name' => $clubUser->club->name,
                'description' => $clubUser->club->description,
                'image' => $clubUser->club->image,
                'pivot' => [
                    'status' => $clubUser->status,
                    'created_at' => $clubUser->created_at,
                    // add more pivot fields if needed
                ]
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'page' => $clubUsers->currentPage(),
                'limit' => $clubUsers->perPage(),
                'total' => $clubUsers->total(),
            ]
        ]);
    }
}
