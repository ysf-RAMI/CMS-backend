<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at,
            'department' => $this->department,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'clubs' => $this->whenLoaded('clubs', function () {
                return $this->clubs->map(function ($club) {
                    return [
                        'id' => $club->id,
                        'name' => $club->name,
                        'description' => $club->description,
                        'image' => $club->image,
                        'categorie' => $club->categorie,
                        'max_members' => $club->max_members,
                        'created_by' => $club->created_by,
                        'created_at' => $club->created_at,
                        'updated_at' => $club->updated_at,
                        'pivot' => ['role' => $club->pivot->role],
                    ];
                });
            }),
            'events' => $this->whenLoaded('events', function () {
                return $this->events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'club_id' => $event->club_id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'date' => $event->date,
                        'location' => $event->location,
                        'image' => $event->image,
                        'max_participants' => $event->max_participants,
                        'created_by' => $event->created_by,
                        'created_at' => $event->created_at,
                        'updated_at' => $event->updated_at,
                        'status' => $event->status,
                    ];
                });
            }),
        ];
    }
}
