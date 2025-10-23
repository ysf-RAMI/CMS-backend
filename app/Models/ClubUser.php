<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubUser extends Model
{
           protected $table = "club_user";
           protected $fillable = [
            'user_id',
            'club_id',
            'role',
            'status',
            'joined_at',
        ];
        protected $guarded = [
            'id',
        ];

        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function club()
        {
            return $this->belongsTo(Club::class, 'club_id');
        }


}
