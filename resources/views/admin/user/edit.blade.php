@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Ubah Pengguna: {{ $user->name }}</h1>

    <form id="edit-form" action="{{ route('admin.user.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
             <div>
                <label for="nim" class="block text-sm font-medium text-gray-700">NIM (Opsional)</label>
                <input type="text" name="nim" id="nim" value="{{ old('nim', $user->nim) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
             <div>
                <label for="jurusan" class="block text-sm font-medium text-gray-700">Jurusan (Opsional)</label>
                <input type="text" name="jurusan" id="jurusan" value="{{ old('jurusan', $user->jurusan) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
             <div class="md:col-span-2">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Peran (Role)</label>
                <select name="role" id="role" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User (Peserta)</option>
                    <option value="trainer" {{ $user->role == 'trainer' ? 'selected' : '' }}>Trainer</option>
                    <option value="proctor" {{ $user->role == 'proctor' ? 'selected' : '' }}>Proctor</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
        </div>
    </form>
    
    <div class="flex justify-between items-center mt-8 pt-5 border-t">
        <div>
            <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus pengguna ini secara permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">Hapus Pengguna</button>
            </form>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.user.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold">Batal</a>
            <button type="submit" form="edit-form" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </div>
</div>
@endsection
