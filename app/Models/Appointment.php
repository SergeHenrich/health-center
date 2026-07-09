<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $fillable = [
        'patient_id','doctor_id','receptionist_id','appointment_date',
        'appointment_time','duration_minutes','type','status',
        'reason','notes','cancellation_reason',
    ];
    protected $casts = [
        'appointment_date' => 'date',
    ];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(User::class, 'doctor_id'); }
    public function receptionist(): BelongsTo { return $this->belongsTo(User::class, 'receptionist_id'); }
    public function consultation(): HasOne { return $this->hasOne(Consultation::class); }
    public function confirm(): void { $this->update(['status' => 'confirmed']); }
    public function cancel(string $reason): void { $this->update(['status' => 'cancelled', 'cancellation_reason' => $reason]); }
    public function markAsArrived(): void { $this->update(['status' => 'arrived']); }
    public function scopeUpcoming($q) { return $q->where('appointment_date', '>=', today())->where('status','!=','cancelled'); }
    public function scopeByDoctor($q, int $doctorId) { return $q->where('doctor_id', $doctorId); }
    public function scopeToday($q) { return $q->whereDate('appointment_date', today()); }
}
