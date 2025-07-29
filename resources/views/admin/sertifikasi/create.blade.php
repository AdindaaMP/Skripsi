@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Adakan Kegiatan Baru</h1>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Terjadi Kesalahan</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form untuk membuat kegiatan baru --}}
    <form action="{{ route('admin.sertifikasi.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Contoh: Microsoft Office Specialist (MOS)">
            </div>

            {{-- Tipe Sertifikasi --}}
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Sertifikasi</label>
                <select id="type" name="type" class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="mos" {{ old('type') == 'mos' ? 'selected' : '' }}>MOS (Microsoft Office Specialist)</option>
                    <option value="mcf" {{ old('type') == 'mcf' ? 'selected' : '' }}>MCF (Microsoft Certified Fundamentals)</option>
                </select>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="description" name="description" rows="4" 
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">URL Logo (Opsional)</label>
                <input type="text" name="logo" id="logo" value="{{ old('logo') }}" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Contoh: /assets/MOS.png">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="registration_start" class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai Pengisian Evaluasi</label>
                    <input type="datetime-local" name="registration_start" id="registration_start" 
                           value="{{ old('registration_start') }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="registration_end" class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai Pengisian Evaluasi</label>
                    <input type="datetime-local" name="registration_end" id="registration_end"
                           value="{{ old('registration_end') }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end items-center gap-4 mt-8 pt-5 border-t">
            <a href="{{ route('admin.sertifikasi.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">
                Batal
            </a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Simpan Kegiatan
            </button>
        </div>
    </form>
</div>
@endsection
