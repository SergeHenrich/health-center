<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'bed_number',
        'type',
        'status',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
