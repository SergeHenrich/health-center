<?php

namespace App\Services;

use App\Models\Appointment;

class AppointmentService
{
    /**
     * Check if a doctor has a slot conflict.
     */
    public function hasConflict(int $doctorId, string $date, string $time, int $duration = 30, ?int $excludeId = null): bool
    {
        $query = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $startA = strtotime("{$date} {$time}");
        $endA   = $startA + ($duration * 60);

        foreach ($query->get() as $appt) {
            $startB = strtotime("{$date} {$appt->appointment_time}");
            $endB   = $startB + ($appt->duration_minutes * 60);

            if ($startA < $endB && $endA > $startB) {
                return true;
            }
        }

        return false;
    }
}
