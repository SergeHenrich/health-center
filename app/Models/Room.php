<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_number',
        'name',
        'type',
        'floor',
        'capacity',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
