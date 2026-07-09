<?php

namespace App\Services;

use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabResult;
use Illuminate\Support\Facades\DB;

class LabService
{
    /**
     * Submit a lab request with exam items.
     */
    public function submitRequest(array $data): LabRequest
    {
        return DB::transaction(function () use ($data) {
            $number = $this->generateRequestNumber();

            $request = LabRequest::create([
                'consultation_id' => $data['consultation_id'],
                'patient_id'      => $data['patient_id'],
                'doctor_id'       => $data['doctor_id'],
                'request_number'  => $number,
                'requested_at'    => now(),
                'urgency'         => $data['urgency'] ?? 'normal',
                'clinical_info'   => $data['clinical_info'] ?? null,
                'status'          => 'pending',
            ]);

            foreach ($data['exam_ids'] as $examId) {
                LabRequestItem::create([
                    'lab_request_id' => $request->id,
                    'lab_exam_id'    => $examId,
                    'status'         => 'pending',
                ]);
            }

            // Update consultation status
            $request->consultation->update(['status' => 'pending_lab']);

            return $request->load('items.labExam', 'patient');
        });
    }

    /**
     * Record a lab result for a request item.
     */
    public function recordResult(array $data): LabResult
    {
        return DB::transaction(function () use ($data) {
            $item = LabRequestItem::findOrFail($data['lab_request_item_id']);

            $result = LabResult::create([
                'lab_request_item_id' => $item->id,
                'lab_request_id'      => $item->lab_request_id,
                'technician_id'       => auth()->id(),
                'result_value'        => $data['result_value'],
                'unit'                => $data['unit'] ?? null,
                'reference_range'     => $data['reference_range'] ?? null,
                'interpretation'      => $data['interpretation'],
                'performed_at'        => now(),
                'notes'               => $data['notes'] ?? null,
            ]);

            $item->update(['status' => 'done']);

            // Check if all items are done
            $allDone = $item->labRequest->items()->where('status', '!=', 'done')->doesntExist();

            if ($allDone) {
                $item->labRequest->markAsCompleted();
                // Reopen the consultation
                $item->labRequest->consultation->update(['status' => 'open']);
            }

            return $result;
        });
    }

    /**
     * Validate a result by a doctor.
     */
    public function validateResult(int $resultId): LabResult
    {
        $result = LabResult::findOrFail($resultId);
        $result->validate(auth()->user());
        return $result;
    }

    /**
     * Generate a unique lab request number like LAB-2024-00001.
     */
    private function generateRequestNumber(): string
    {
        $year = now()->year;
        $base = "LAB-{$year}-";
        $last = LabRequest::where('request_number', 'like', "{$base}%")
            ->orderByDesc('request_number')->value('request_number');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $base . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
