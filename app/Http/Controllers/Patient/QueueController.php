<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(): View
    {
        $queues = Queue::with(['patient', 'doctor', 'appointment'])
            ->whereDate('created_at', today())
            ->orderBy('queue_number')
            ->get()
            ->groupBy('service');

        $patients = Patient::active()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'patient_code']);
        $doctors  = User::role(['general_practitioner', 'specialist'])->active()->get(['id', 'first_name', 'last_name']);

        return view('queue.index', compact('queues', 'patients', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id'     => 'required|exists:patients,id',
            'service'        => 'required|in:consultation,lab,pharmacy,cashier',
            'doctor_id'      => 'nullable|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $nextNumber = Queue::whereDate('created_at', today())
            ->where('service', $validated['service'])
            ->max('queue_number') + 1;

        Queue::create([
            ...$validated,
            'queue_number' => $nextNumber,
            'status'       => 'waiting',
            'arrived_at'   => now(),
        ]);

        return back()->with('success', "Patient ajouté à la file d'attente (N° {$nextNumber}).");
    }

    public function call(Queue $queue): RedirectResponse
    {
        $queue->call();
        return back()->with('success', "Patient N° {$queue->queue_number} appelé.");
    }

    public function complete(Queue $queue): RedirectResponse
    {
        $queue->complete();
        return back()->with('success', "Patient N° {$queue->queue_number} servi.");
    }
}
