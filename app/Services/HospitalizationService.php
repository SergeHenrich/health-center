<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\Hospitalization;
use Illuminate\Support\Facades\DB;

class HospitalizationService
{
    /**
     * Admit a patient to a bed.
     */
    public function admit(array $data): Hospitalization
    {
        return DB::transaction(function () use ($data) {
            $bed = Bed::findOrFail($data['bed_id']);

            if (!$bed->isAvailable()) {
                throw new \RuntimeException("Bed #{$bed->bed_number} is not available.");
            }

            $bed->occupy();

            $number = $this->generateAdmissionNumber();

            $hospitalization = Hospitalization::create([
                'patient_id'          => $data['patient_id'],
                'room_id'             => $data['room_id'],
                'bed_id'              => $data['bed_id'],
                'admitting_doctor_id' => $data['doctor_id'],
                'attending_nurse_id'  => $data['nurse_id'] ?? null,
                'admission_number'    => $number,
                'admission_date'      => now(),
                'reason_for_admission'=> $data['reason'],
                'status'              => 'admitted',
            ]);

            return $hospitalization->load(['patient', 'room', 'bed', 'admittingDoctor']);
        });
    }

    /**
     * Discharge a patient.
     */
    public function discharge(int $id, array $data): Hospitalization
    {
        return DB::transaction(function () use ($id, $data) {
            $hospitalization = Hospitalization::findOrFail($id);

            $hospitalization->discharge(
                $data['discharge_summary'],
                $data['discharge_condition']
            );

            // Release the bed
            $hospitalization->bed->release();

            return $hospitalization->fresh();
        });
    }

    /**
     * Generate a unique admission number like ADM-2024-00001.
     */
    private function generateAdmissionNumber(): string
    {
        $year = now()->year;
        $base = "ADM-{$year}-";
        $last = Hospitalization::where('admission_number', 'like', "{$base}%")
            ->orderByDesc('admission_number')->value('admission_number');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $base . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
