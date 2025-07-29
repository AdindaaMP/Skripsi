@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kegiatan Sertifikasi</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.sertifikasi.create') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-400">
                Adakan Kegiatan Baru
            </a>
            <div class="relative">
                <button onclick="toggleProgramDropdown()" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 flex items-center">
                    Kelola Kuesioner Program
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="programDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                    @foreach(\App\Models\Program::all() as $program)
                        <a href="{{ route('admin.program.questionnaires.index', $program->id) }}" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-200 last:border-b-0">
                            {{ $program->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($certifications as $activity)
            <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div class="flex items-center space-x-4">
                    <img src="{{ asset($activity->logo) }}" alt="Logo" class="h-14 w-14 object-contain rounded-md bg-gray-100 p-1">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $activity->name }}</h2>
                        <p class="text-sm text-gray-600">{{ Str::limit($activity->description, 80) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Total Pendaftar: {{ $activity->total_users }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.sertifikasi.show', $activity->id) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 flex-shrink-0">
                    Detail
                </a>
            </div>
        @empty
            <div class="bg-white p-6 rounded-lg shadow-sm text-center text-gray-500">
                <p>Tidak ada kegiatan sertifikasi yang tersedia.</p>
            </div>
        @endforelse
    </div>

    <script>
        function toggleProgramDropdown() {
            const dropdown = document.getElementById('programDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Tutup dropdown saat klik di luar
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('programDropdown');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().includes('toggleProgramDropdown')) {
                return;
            }
            
            if (!dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
@endsection