<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'doctor_id',
        'service',
        'queue_number',
        'status',
        'arrived_at',
        'called_at',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'arrived_at' => 'datetime',
            'called_at'  => 'datetime',
            'served_at'  => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function call(): void
    {
        $this->update(['status' => 'called', 'called_at' => now()]);
    }

    public function complete(): void
    {
        $this->update(['status' => 'served', 'served_at' => now()]);
    }
}
