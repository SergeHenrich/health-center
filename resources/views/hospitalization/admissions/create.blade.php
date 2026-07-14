@extends('layouts.app')
@section('title', 'Nouvelle admission')
@section('page-title', 'Admettre un patient')

@section('content')
<div class="max-w-3xl space-y-6">

    <form method="POST" action="{{ route('hospitalizations.store') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Patient *</label>
                <select name="patient_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— Sélectionner —</option>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->last_name }} {{ $p->first_name }} ({{ $p->patient_code }})
                    </option>
                    @endforeach
                </select>
                @error('patient_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Médecin admitteur *</label>
                <select name="doctor_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— Sélectionner —</option>
                    @foreach($doctors as $d)
                    <option value="{{ $d->id }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                        Dr. {{ $d->last_name }} {{ $d->first_name }}
                    </option>
                    @endforeach
                </select>
                @error('doctor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chambre *</label>
                <select name="room_id" id="room-select" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— Sélectionner —</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" data-beds="{{ $room->beds->filter->isAvailable()->toJson() }}">
                        {{ $room->room_number }} — {{ $room->type }} ({{ $room->beds->filter->isAvailable()->count() }} lit(s) libre(s))
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lit *</label>
                <select name="bed_id" id="bed-select" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— Sélectionner une chambre d'abord —</option>
                </select>
                @error('bed_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Infirmier(ère) assigné(e)</label>
            <select name="nurse_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">— Aucun(e) —</option>
                @foreach($nurses as $n)
                <option value="{{ $n->id }}" {{ old('nurse_id') == $n->id ? 'selected' : '' }}>
                    {{ $n->last_name }} {{ $n->first_name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motif d'admission *</label>
            <textarea name="reason" rows="3" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                      placeholder="Motif médical de l'admission...">{{ old('reason') }}</textarea>
            @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('hospitalizations.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-xl text-sm text-white font-medium">
                <i class="fa-solid fa-bed mr-1"></i> Admettre le patient
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('room-select')?.addEventListener('change', function() {
    const bedSelect = document.getElementById('bed-select');
    const option = this.options[this.selectedIndex];
    bedSelect.innerHTML = '<option value="">— Sélectionner —</option>';
    try {
        const beds = JSON.parse(option.dataset.beds || '[]');
        beds.forEach(bed => {
            const opt = document.createElement('option');
            opt.value = bed.id;
            opt.textContent = bed.bed_number + ' (' + bed.type + ')';
            bedSelect.appendChild(opt);
        });
    } catch(e) {}
});
</script>
@endpush
@endsection
