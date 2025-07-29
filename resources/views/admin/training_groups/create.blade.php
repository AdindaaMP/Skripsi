@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    
    {{-- Judul nama kegiatan --}}
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Tambah Kelompok Baru</h1>
    <p class="text-gray-600 mb-6">untuk kegiatan: <span class="font-semibold">{{ $sertifikasi->name }}</span></p>

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

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Form untuk membuat kelompok baru --}}
    <form action="{{ route('admin.sertifikasi.groups.store', $sertifikasi->id) }}" method="POST">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kelompok</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Contoh: Kelompok Pagi A, Kelompok Sore B, dll.">
            </div>

            <div>
                <label for="program_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Program</label>
                <select name="program_id" id="program_id" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">-- Pilih Program --</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="kuota" class="block text-sm font-medium text-gray-700 mb-1">Kuota Peserta</label>
                <input type="number" name="kuota" id="kuota" value="{{ old('kuota', 10) }}" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Jumlah maksimal peserta">
            </div>
        </div>

        <div class="flex justify-end items-center gap-4 mt-8 pt-5 border-t">
            <a href="{{ route('admin.sertifikasi.show', $sertifikasi->id) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">
                Batal
            </a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Simpan Kelompok
            </button>
        </div>
    </form>
</div>
@endsection
