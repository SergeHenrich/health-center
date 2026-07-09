<?php

namespace App\Services\Pharmacy;

use App\Models\MedicationEvent;
use Illuminate\Support\Facades\DB;

class MedicationEventService
{
    public function getEvents(array $filters = [])
    {
        return MedicationEvent::with(['medicine', 'patient', 'reporter'])
            ->when($filters['type'] ?? null, fn($q, $v) => $q->where('type', $v))
            ->when($filters['severity'] ?? null, fn($q, $v) => $q->where('severity', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('description', 'like', "%{$v}%"))
            ->latest('occurred_at')
            ->paginate(25);
    }

    public function reportEvent(array $data): MedicationEvent
    {
        return DB::transaction(function () use ($data) {
            return MedicationEvent::create([
                'type' => $data['type'],
                'severity' => $data['severity'] ?? 'medium',
                'status' => 'open',
                'medicine_id' => $data['medicine_id'] ?? null,
                'patient_id' => $data['patient_id'] ?? null,
                'prescription_id' => $data['prescription_id'] ?? null,
                'dispensation_id' => $data['dispensation_id'] ?? null,
                'reported_by' => $data['reported_by'] ?? auth()->id(),
                'assigned_to' => $data['assigned_to'] ?? null,
                'description' => $data['description'],
                'cause' => $data['cause'] ?? null,
                'action_taken' => $data['action_taken'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function resolveEvent(MedicationEvent $event, string $resolution, ?string $correctiveActions = null): MedicationEvent
    {
        return DB::transaction(function () use ($event, $resolution, $correctiveActions) {
            $event->update([
                'status' => 'resolved',
                'action_taken' => $resolution,
                'corrective_actions' => $correctiveActions,
                'resolved_at' => now(),
            ]);

            return $event;
        });
    }

    public function assignEvent(MedicationEvent $event, int $userId): MedicationEvent
    {
        $event->update(['assigned_to' => $userId]);
        return $event;
    }
}
