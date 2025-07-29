@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Ubah Data Proctor</h1>

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.proctor.update', $proctor->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name', $proctor->name) }}" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $proctor->email) }}" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-700">URL Foto (Opsional)</label>
                <input type="text" name="avatar" id="avatar" value="{{ old('avatar', $proctor->avatar) }}" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md @error('avatar') border-red-500 @enderror">
                @error('avatar')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex justify-end gap-4 mt-6">
            <a href="{{ route('admin.proctor.show', $proctor->id) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
