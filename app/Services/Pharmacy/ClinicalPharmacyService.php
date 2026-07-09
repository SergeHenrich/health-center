<?php

namespace App\Services\Pharmacy;

use App\Models\PharmaceuticalIntervention;
use App\Models\PharmaceuticalValidation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Support\Facades\DB;

class ClinicalPharmacyService
{
    public function validatePrescription(Prescription $prescription, string $status = 'approved', ?string $notes = null): PharmaceuticalValidation
    {
        return DB::transaction(function () use ($prescription, $status, $notes) {
            $interventions = [];

            foreach ($prescription->items as $item) {
                $issues = $this->analyzePrescriptionItem($item);
                $interventions = array_merge($interventions, $issues);
            }

            $validation = PharmaceuticalValidation::create([
                'prescription_id' => $prescription->id,
                'pharmacist_id' => auth()->id(),
                'validated_at' => now(),
                'status' => $status,
                'notes' => $notes,
            ]);

            foreach ($interventions as $issue) {
                $validation->interventions()->create($issue);
            }

            if ($status === 'approved' && count($interventions) === 0) {
                $prescription->update(['status' => 'validated']);
            } elseif ($status === 'approved' && count($interventions) > 0) {
                $prescription->update(['status' => 'validated_with_interventions']);
            }

            return $validation->load('interventions');
        });
    }

    public function recordIntervention(
        PharmaceuticalValidation $validation,
        array $data
    ): PharmaceuticalIntervention {
        return $validation->interventions()->create([
            'prescription_item_id' => $data['prescription_item_id'] ?? null,
            'intervention_type' => $data['intervention_type'],
            'severity' => $data['severity'] ?? 'minor',
            'description' => $data['description'],
            'action_taken' => $data['action_taken'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);
    }

    public function getPendingValidations()
    {
        return Prescription::with(['patient', 'doctor', 'items.medicine'])
            ->whereIn('status', ['pending', 'validated_with_interventions'])
            ->latest('issued_at')
            ->get();
    }

    public function getValidationHistory(Prescription $prescription)
    {
        return $prescription->pharmaceuticalValidations()
            ->with(['pharmacist', 'interventions'])
            ->latest('validated_at')
            ->get();
    }

    private function analyzePrescriptionItem(PrescriptionItem $item): array
    {
        $issues = [];
        $medicine = $item->medicine;

        if (!$medicine) {
            return $issues;
        }

        if ($medicine->formulary_status === 'non_inscrit') {
            $issues[] = [
                'prescription_item_id' => $item->id,
                'intervention_type' => 'non_formulary',
                'severity' => 'major',
                'description' => "{$medicine->name} n'est pas inscrit au livret thérapeutique.",
                'status' => 'pending',
            ];
        }

        if ($medicine->is_narcotic) {
            $issues[] = [
                'prescription_item_id' => $item->id,
                'intervention_type' => 'narcotic_alert',
                'severity' => 'moderate',
                'description' => "{$medicine->name} est un stupéfiant. Vérifier la conformité de l'ordonnance sécurisée.",
                'status' => 'pending',
            ];
        }

        $substitutions = $medicine->therapeuticSubstitutions()
            ->where('is_active', true)
            ->with('substitute')
            ->get();

        foreach ($substitutions as $sub) {
            $issues[] = [
                'prescription_item_id' => $item->id,
                'intervention_type' => 'substitution_available',
                'severity' => 'minor',
                'description' => "Substitution possible par {$sub->substitute->name} ({$sub->substitution_type}).",
                'status' => 'pending',
            ];
        }

        return $issues;
    }
}
