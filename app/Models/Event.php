<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     title="Event",
 *     description="Event model",
 *     @OA\Xml(name="Event"),
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Unique identifier for the event",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="club_id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the club organizing the event"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title of the event"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the event"
 *     ),
 *     @OA\Property(
 *         property="date",
 *         type="string",
 *         format="date",
 *         description="Date of the event"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="string",
 *         description="Location of the event"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Image URL of the event (optional)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="max_participants",
 *         type="integer",
 *         description="Maximum number of participants for the event"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="string",
 *         format="uuid",
 *         description="ID of the user who created the event",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "approved", "rejected"},
 *         description="Status of the event",
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the event was created",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the event was last updated",
 *         readOnly=true
 *     )
 * )
 */
class Event extends Model
{
    use HasFactory;

    protected $table = 'event';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = [
        'club_id',
        'title',
        'description',
        'date',
        'location',
        'image',
        'max_participants',
        'created_by',
        'status'
    ];

    protected $appends = ['club_info'];

    /**
     * Get the event's image with default fallback.
     */
    public function getImageAttribute($value)
    {
        return $value ?: '/images/default_event_image.jpg';
    }

    public function getClubInfoAttribute()
    {
        return $this->club ? $this->club->only('id', 'name') : null;
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'event_registration')
            ->withPivot('status')
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }
}