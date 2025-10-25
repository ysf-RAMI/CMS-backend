<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     title="Club",
 *     description="Club model",
 *     @OA\Xml(name="Club"),
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="UUID of the club"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the club"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the club"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Image URL of the club"
 *     ),
 *     @OA\Property(
 *         property="categorie",
 *         type="string",
 *         description="Category of the club"
 *     ),
 *     @OA\Property(
 *         property="max_members",
 *         type="integer",
 *         description="Maximum number of members in the club"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="string",
 *         format="uuid",
 *         description="UUID of the user who created the club"
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
        return $this->belongsToMany(User::class, 'club_user', 'club_id', 'user_id')->withPivot('role');
    }
    /**
     * The events that belong to the Club
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'club_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
        });
    }

}