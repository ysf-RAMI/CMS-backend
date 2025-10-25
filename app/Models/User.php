<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
