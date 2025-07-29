@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manajemen Proctor</h1>
    <a href="{{ route('admin.proctor.create') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-400">
        Tambah Proctor
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-sm">
    <table class="w-full text-sm text-left text-gray-600">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="py-3 px-4">Nama</th>
                <th class="py-3 px-4">Email</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($proctors as $proctor)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 flex items-center gap-3">
                        <img src="{{ $proctor->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($proctor->name) }}" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                        <span class="font-medium text-gray-900">{{ $proctor->name }}</span>
                    </td>
                    <td class="py-3 px-4">{{ $proctor->email }}</td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('admin.proctor.show', $proctor->id) }}" class="bg-cyan-500 text-white px-4 py-1 rounded-full text-xs font-semibold hover:bg-cyan-600">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4">Belum ada proctor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
