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

    public function currentHospitalization()
    {
        return $this->hasOne(Hospitalization::class)->latestOfMany();
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function occupy(): void
    {
        $this->update(['status' => 'occupied']);
    }

    public function release(): void
    {
        $this->update(['status' => 'available']);
    }
}
