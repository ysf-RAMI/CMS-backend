<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Club extends Model
{
    use HasFactory;

    protected $table = "club";

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
        return $this->belongsToMany(User::class);
    }
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }
}
