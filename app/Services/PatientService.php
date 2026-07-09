<?php

namespace App\Services;

use App\Exceptions\PatientNotFoundException;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Traits\GeneratesCode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientService
{
    /**
     * Register a new patient and create their medical record.
     */
    public function register(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            $data['patient_code'] = $this->generateUniqueCode();

            $patient = Patient::create($data);

            MedicalRecord::create([
                'patient_id'    => $patient->id,
                'record_number' => 'MR-' . str_pad($patient->id, 6, '0', STR_PAD_LEFT),
                'blood_type'    => $data['blood_type'] ?? null,
                'height_cm'     => $data['height_cm'] ?? null,
                'weight_kg'     => $data['weight_kg'] ?? null,
            ]);

            return $patient->load('medicalRecord');
        });
    }

    /**
     * Update patient demographic data.
     */
    public function update(int $id, array $data): Patient
    {
        $patient = $this->findOrFail($id);

        return DB::transaction(function () use ($patient, $data) {
            $patient->update($data);
            return $patient->fresh();
        });
    }

    /**
     * Soft-delete a patient.
     */
    public function delete(int $id): void
    {
        $patient = $this->findOrFail($id);
        $patient->delete();
    }

    /**
     * Search and paginate patients.
     */
    public function search(?string $term, int $perPage = 20): LengthAwarePaginator
    {
        $query = Patient::with('medicalRecord')->active();

        if ($term) {
            $query->search($term);
        }

        return $query->orderBy('last_name')->paginate($perPage);
    }

    /**
     * Find a patient by ID or throw.
     *
     * @throws PatientNotFoundException
     */
    public function findOrFail(int $id): Patient
    {
        $patient = Patient::with(['medicalRecord', 'appointments', 'invoices'])->find($id);

        if (!$patient) {
            throw new PatientNotFoundException("Patient #{$id} not found.");
        }

        return $patient;
    }

    /**
     * Generate a unique patient code like PAT-2024-00042.
     */
    public function generateUniqueCode(): string
    {
        $year  = now()->year;
        $base  = "PAT-{$year}-";
        $last  = Patient::where('patient_code', 'like', "{$base}%")
                    ->orderByDesc('patient_code')
                    ->value('patient_code');
        $next  = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $base . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
