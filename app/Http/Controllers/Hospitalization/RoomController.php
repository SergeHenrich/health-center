<?php

namespace App\Http\Controllers\Hospitalization;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::with(['beds'])->active()->orderBy('room_number')->paginate(20);
        return view('hospitalization.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('hospitalization.rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:20|unique:rooms,room_number',
            'name'        => 'nullable|string|max:100',
            'type'        => 'required|in:standard,intensive_care,surgery,maternity,emergency,isolation',
            'floor'       => 'nullable|string|max:20',
            'capacity'    => 'required|integer|min:1|max:50',
            'notes'       => 'nullable|string|max:500',
        ]);

        $room = Room::create($validated);

        // Auto-create beds
        for ($i = 1; $i <= $validated['capacity']; $i++) {
            $room->beds()->create([
                'bed_number' => $validated['room_number'] . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'type'       => 'standard',
                'status'     => 'available',
            ]);
        }

        return redirect()->route('rooms.index')
            ->with('success', "Chambre {$room->room_number} créée avec {$validated['capacity']} lit(s).");
    }

    public function show(Room $room): View
    {
        $room->load(['beds.currentHospitalization.patient']);
        return view('hospitalization.rooms.show', compact('room'));
    }
}
