@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Ubah Kelompok</h1>
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

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Berhasil!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('admin.sertifikasi.groups.update', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id]) }}" method="POST" id="edit-form">
        @csrf
        @method('PUT')
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kelompok</label>
                <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="kuota" class="block text-sm font-medium text-gray-700 mb-1">Kuota Peserta</label>
                <input type="number" name="kuota" id="kuota" value="{{ old('kuota', $group->kuota) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div>
                <label for="program_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Program</label>
                <select name="program_id" id="program_id" required class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">-- Pilih Program --</option>
                    @foreach (\App\Models\Program::all() as $program)
                        <option value="{{ $program->id }}" {{ (old('program_id', $group->program_id) == $program->id) ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="trainer_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Trainer</label>
                <select id="trainer_id" name="trainer_id" class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Tidak Ada Trainer</option>
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}" {{ $group->trainers->contains($trainer->id) ? 'selected' : '' }}>{{ $trainer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="proctor_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Proctor</label>
                <select id="proctor_id" name="proctor_id" class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Tidak Ada Proctor</option> 
                    @foreach ($proctors as $proctor)
                        <option value="{{ $proctor->id }}" {{ $group->proctors->contains($proctor->id) ? 'selected' : '' }}>{{ $proctor->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="flex justify-between items-center mt-8 pt-5 border-t">
        <div>
            <form action="{{ route('admin.sertifikasi.groups.destroy', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok ini? Semua data terkait akan hilang permanen.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">
                    Hapus Kelompok
                </button>
            </form>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.sertifikasi.groups.show', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id]) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">Batal</a>
            <button type="submit" form="edit-form" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </div>
</div>
@endsection
