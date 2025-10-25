<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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