<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    use HasFactory;

    protected $table = "club";
    protected $primaryKey = 'id'; 
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'image',
        'categorie',
        'max_members',
        'created_by',
    ];

    /**
     * The users that belong to the Club
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_user', 'club_id', 'user_id');
    }
    /**
     * The events that belong to the Club
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'club_id');
    }

}