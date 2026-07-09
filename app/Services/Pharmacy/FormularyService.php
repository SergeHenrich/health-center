<?php

namespace App\Services\Pharmacy;

use App\Models\FormularyCommissionDecision;
use App\Models\Medicine;
use App\Models\TherapeuticSubstitution;
use Illuminate\Support\Facades\DB;

class FormularyService
{
    public function getFormulary(array $filters = [])
    {
        return Medicine::with('stock')
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('formulary_status', $v))
            ->when($filters['therapeutic_class'] ?? null, fn($q, $v) => $q->where('therapeutic_class', $v))
            ->when($filters['q'] ?? null, fn($q, $v) => $q->search($v))
            ->when($filters['narcotic'] ?? null, fn($q) => $q->narcotic())
            ->orderBy('name')
            ->paginate(25);
    }

    public function addToFormulary(Medicine $medicine, array $data): Medicine
    {
        $medicine->update([
            'formulary_status' => $data['formulary_status'] ?? $data['status'] ?? 'inscrit',
            'atc_code' => $data['atc_code'] ?? $medicine->atc_code,
            'therapeutic_class' => $data['therapeutic_class'] ?? $medicine->therapeutic_class,
            'is_narcotic' => filter_var($data['is_narcotic'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_psychotropic' => filter_var($data['is_psychotropic'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);

        return $medicine;
    }

    public function recordCommissionDecision(Medicine $medicine, array $data): FormularyCommissionDecision
    {
        return DB::transaction(function () use ($medicine, $data) {
            $decision = FormularyCommissionDecision::create([
                'medicine_id' => $medicine->id,
                'decided_by' => $data['decided_by'] ?? auth()->id(),
                'decision' => $data['decision'],
                'justification' => $data['justification'] ?? null,
                'decision_date' => $data['decision_date'] ?? now(),
                'review_date' => $data['review_date'] ?? null,
                'reference_document' => $data['reference_document'] ?? null,
            ]);

            if (in_array($data['decision'], ['admission', 'renewal'])) {
                $medicine->update(['formulary_status' => 'inscrit']);
            } elseif ($data['decision'] === 'rejection') {
                $medicine->update(['formulary_status' => 'non_inscrit']);
            } elseif ($data['decision'] === 'removal') {
                $medicine->update(['formulary_status' => 'retire']);
            }

            return $decision;
        });
    }

    public function registerSubstitution(Medicine $medicine, Medicine $substitute, string $type = 'generic', ?string $reason = null): TherapeuticSubstitution
    {
        return TherapeuticSubstitution::create([
            'medicine_id' => $medicine->id,
            'substitute_medicine_id' => $substitute->id,
            'substitution_type' => $type,
            'reason' => $reason,
            'is_active' => true,
        ]);
    }

    public function getSubstitutions(Medicine $medicine)
    {
        return TherapeuticSubstitution::where('medicine_id', $medicine->id)
            ->orWhere('substitute_medicine_id', $medicine->id)
            ->with(['medicine', 'substitute'])
            ->get();
    }

    public function deactivateSubstitution(TherapeuticSubstitution $substitution): void
    {
        $substitution->update(['is_active' => false]);
    }

    public function getCommissionHistory(Medicine $medicine)
    {
        return $medicine->commissionDecisions()
            ->with('decidedBy')
            ->latest('decision_date')
            ->get();
    }
}
