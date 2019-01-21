<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $hidden = [
       'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scoreActivity()
    {
        return $this->hasMany('App\ScoreActivity');
    }
}
