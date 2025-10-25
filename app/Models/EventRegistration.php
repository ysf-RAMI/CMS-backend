<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     title="EventRegistration",
 *     description="Event Registration model",
 *     @OA\Xml(name="EventRegistration"),
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Unique identifier for the event registration",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="event_id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the event being registered for"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the user registering for the event"
 *     ),
 *     @OA\Property(
 *         property="registered_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the user registered for the event",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "approved", "rejected"},
 *         description="Status of the event registration",
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the event registration was created",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the event registration was last updated",
 *         readOnly=true
 *     )
 * )
 */
class EventRegistration extends Model
{
    use HasFactory;

    protected $table = 'event_registration';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'id' => 'string',
        'registered_at' => 'datetime',
    ];

    protected $fillable = [
        'event_id',
        'user_id',
        'registered_at',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}