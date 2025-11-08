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
 *     title="User",
 *     description="User model",
 *     @OA\Xml(name="User"),
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="UUID of the user"
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
 *         description="Email of the user"
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
 *         description="Image URL of the user"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     )
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's image with default fallback.
     */
    public function getImageAttribute($value)
    {
        return $value ?: '/images/default_user_image.jpg';
    }

    // Relationship with clubs (many-to-many)
    public function clubs(): BelongsToMany
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
