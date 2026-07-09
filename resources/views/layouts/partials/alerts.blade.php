@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        <span class="text-sm">{{ session('success') }}</span>
        <button @click="show = false" class="ml-auto text-green-400 hover:text-green-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" x-show="show"
         class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
        <span class="text-sm">{{ session('error') }}</span>
        <button @click="show = false" class="ml-auto text-red-400 hover:text-red-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
        <div class="flex items-center gap-2 mb-2">
            <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
            <span class="text-sm font-semibold">Erreurs de validation :</span>
        </div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
