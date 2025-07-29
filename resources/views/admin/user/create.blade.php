@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Tambah Pengguna Baru</h1>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.user.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
             <div>
                <label for="nim" class="block text-sm font-medium text-gray-700">NIM (Opsional)</label>
                <input type="text" name="nim" id="nim" value="{{ old('nim') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
             <div>
                <label for="jurusan" class="block text-sm font-medium text-gray-700">Jurusan (Opsional)</label>
                <input type="text" name="jurusan" id="jurusan" value="{{ old('jurusan') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
             <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Peran (Role)</label>
                <select name="role" id="role" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Pilih Peran</option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (Peserta)</option>
                    <option value="trainer" {{ old('role') == 'trainer' ? 'selected' : '' }}>Trainer</option>
                    <option value="proctor" {{ old('role') == 'proctor' ? 'selected' : '' }}>Proctor</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih satu peran untuk pengguna ini</p>
            </div>
        </div>
        <div class="flex justify-end gap-4 mt-6">
            <a href="{{ route('admin.user.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                Simpan Pengguna
            </button>
        </div>
    </form>
</div>
@endsection
