<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScoreActivity extends Model
{
    protected $hidden = [
        'updated_at'
    ];

    protected $fillable = [
        'user_id', 'photo_id', 'activity'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function photo()
    {
        return $this->belongsTo('App\Photo');
    }
}
