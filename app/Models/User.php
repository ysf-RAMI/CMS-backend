<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Unique identifier for the user"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the user"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email address of the user"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Hashed password of the user"
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="string",
 *         enum={"admin", "admin-member", "member", "student"},
 *         description="Role of the user"
 *     ),
 *     @OA\Property(
 *         property="department",
 *         type="string",
 *         description="Department of the user"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Path to the user's profile image"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time when the user was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time when the user was last updated"
 *     )
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'user';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'department', 'image'
    ];

    // Relationship with clubs (many-to-many)
    public function clubs():
        BelongsToMany
    {
        return $this->belongsToMany(Club::class)
            ->withPivot('role'); // Include 'role' from pivot table
    }

    // Relationship with events (many-to-many)
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_registration', 'user_id', 'event_id')
            ->withPivot('status'); // Include 'status' from pivot table
    }

    // JWT required methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
