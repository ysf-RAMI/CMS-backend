<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClubUser extends Model
{
    use HasUuids;

    protected $table = "club_user";
    protected $fillable = [
        'user_id',
        'club_id',
        'role',
        'status',
        'joined_at',
    ];
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }


  

}
