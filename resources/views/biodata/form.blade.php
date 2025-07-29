@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <img src="{{ asset('assets/logo-no_text.png') }}" alt="Logo" class="h-16 w-auto">
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Biodata Anda
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Sistem akan mencoba mengisi biodata otomatis dari email Anda
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Jika biodata sudah terisi otomatis, tampilkan field readonly dan tombol Next --}}
            @if ($user->nim && $user->jurusan)
                <form action="{{ route('biodata.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="nim" value="{{ $user->nim }}">
                    <input type="hidden" name="jurusan" value="{{ $user->jurusan }}">
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="biodata_confirmed" value="1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" value="{{ $user->name }}" readonly class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" value="{{ $user->email }}" readonly class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIM</label>
                        <input type="text" value="{{ $user->nim }}" readonly class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                        <input type="text" value="{{ $user->jurusan }}" readonly class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 sm:text-sm">
                    </div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Lanjutkan ke Dashboard
                    </button>
                </form>
            @else
                {{-- Form manual jika gagal auto-fill --}}
                <form class="space-y-6" action="{{ route('biodata.store') }}" method="POST">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ $user->name }}" readonly class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="{{ $user->email }}" readonly class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="nim" class="block text-sm font-medium text-gray-700">NIM <span class="text-red-500">*</span></label>
                        <input type="text" name="nim" id="nim" value="{{ old('nim', $user->nim) }}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Masukkan NIM Anda" required maxlength="10">
                        <p class="mt-1 text-xs text-gray-500">Contoh: 2331249 (akan diisi otomatis jika email sesuai format)</p>
                    </div>
                    <div>
                        <label for="jurusan_display" class="block text-sm font-medium text-gray-700">Jurusan</label>
                        <input type="text" name="jurusan_display" id="jurusan_display" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500 sm:text-sm" readonly>
                        <p class="mt-1 text-xs text-gray-500">Jurusan akan otomatis terisi berdasarkan NIM</p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="window.location.reload()" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Coba Auto-Fill Lagi
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan Manual
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nimInput = document.getElementById('nim');
    const jurusanDisplay = document.getElementById('jurusan_display');
    
    const jurusanMap = {
        '11': 'S1 Teknik Elektro',
        '12': 'S1 Teknik Mesin',
        '21': 'S1 Teknik Sipil',
        '31': 'S1 Teknik Informatika',
        '71': 'D3 Teknologi Listrik',
        '72': 'D3 Teknik Mesin',
        '32': 'S1 Sistem Informasi',
        '42': 'S1 Teknik Industri',
        '41': 'S1 Bisnis Energi',
        '14': 'S1 Teknik Tenaga Listrik',
        '15': 'S1 Teknik Sistem Energi'
    };
    
    function updateJurusan() {
        const nim = nimInput.value;
        if (nim.length >= 7) {
            const kodeJurusan = nim.substring(4, 6); // Ambil 2 digit dari posisi 5
            const jurusan = jurusanMap[kodeJurusan] || 'Jurusan Tidak Diketahui';
            jurusanDisplay.value = jurusan;
        } else {
            jurusanDisplay.value = '';
        }
    }
    
    nimInput.addEventListener('input', updateJurusan);
    
    // Update jurusan saat halaman dimuat jika NIM sudah ada
    if (nimInput.value) {
        updateJurusan();
    }
});
</script> 