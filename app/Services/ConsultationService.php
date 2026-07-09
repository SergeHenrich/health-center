<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class ConsultationService
{
    /**
     * Open a new consultation for a patient.
     */
    public function open(array $data): Consultation
    {
        return DB::transaction(function () use ($data) {
            $record = MedicalRecord::where('patient_id', $data['patient_id'])->firstOrFail();

            $consultation = Consultation::create([
                'medical_record_id'  => $record->id,
                'doctor_id'          => $data['doctor_id'],
                'appointment_id'     => $data['appointment_id'] ?? null,
                'consultation_date'  => $data['consultation_date'] ?? today(),
                'chief_complaint'    => $data['chief_complaint'],
                'history_of_illness' => $data['history_of_illness'] ?? null,
                'status'             => 'open',
            ]);

            // Update appointment status
            if ($consultation->appointment_id) {
                $consultation->appointment->update(['status' => 'in_progress']);
            }

            return $consultation->load(['doctor', 'medicalRecord.patient']);
        });
    }

    /**
     * Close a consultation.
     */
    public function close(int $id): Consultation
    {
        return DB::transaction(function () use ($id) {
            $consultation = Consultation::findOrFail($id);
            $consultation->close();

            if ($consultation->appointment_id) {
                $consultation->appointment->update(['status' => 'done']);
            }

            return $consultation->fresh();
        });
    }

    /**
     * Add a diagnosis to a consultation.
     */
    public function addDiagnosis(int $consultationId, array $data): \App\Models\Diagnosis
    {
        $consultation = Consultation::findOrFail($consultationId);

        return $consultation->diagnoses()->create($data);
    }

    /**
     * Update clinical notes.
     */
    public function updateNotes(int $id, array $data): Consultation
    {
        $consultation = Consultation::findOrFail($id);
        $consultation->update($data);
        return $consultation->fresh();
    }
}
