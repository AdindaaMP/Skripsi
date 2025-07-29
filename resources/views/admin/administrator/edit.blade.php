@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Ubah Data Administrator</h1>

    <form action="{{ route('admin.administrator.update', $administrator->id) }}" method="POST" id="edit-form">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name', $administrator->name) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $administrator->email) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-700">URL Foto (Opsional)</label>
                <input type="text" name="avatar" id="avatar" value="{{ old('avatar', $administrator->avatar) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>
    </form>
    
    <div class="flex justify-between items-center mt-8 pt-5 border-t">
        <div>
            <form action="{{ route('admin.administrator.destroy', $administrator->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus administrator ini? Role akan dikembalikan menjadi user biasa.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">
                    Hapus
                </button>
            </form>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.administrator.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">Batal</a>
            <button type="submit" form="edit-form" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                Simpan Perubahan
            </button>
        </div>
    </div>

</div>
@endsection
