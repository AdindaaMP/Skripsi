@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Jadikan User sebagai Administrator</h1>

    <form action="{{ route('admin.administrator.store') }}" method="POST">
        @csrf
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih User</label>
            <select name="user_id" id="user_id" class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                @forelse ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @empty
                    <option disabled>Tidak ada user yang bisa dijadikan administrator.</option>
                @endforelse
            </select>
        </div>
        <div class="flex justify-end gap-4 mt-6">
            <a href="{{ route('admin.administrator.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                Jadikan Administrator
            </button>
        </div>
    </form>
</div>
@endsection
