@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-7xl mx-auto">
    
    <div class="flex justify-between items-start mb-6 border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Halaman Pengawasan: {{ $group->name }}</h1>
            <p class="text-gray-600">Kegiatan: <span class="font-semibold">{{ $activity->name }}</span></p>
        </div>
        <a href="{{ route('home.user') }}" class="text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    </div>

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-50 p-6 rounded-lg shadow-sm text-center">
            <p class="text-sm font-medium text-blue-800">Total Peserta</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalPeserta }}</p>
        </div>
        <div class="bg-green-50 p-6 rounded-lg shadow-sm text-center">
            <p class="text-sm font-medium text-green-800">Sudah Mengisi</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $sudahMengisi }}</p>
        </div>
        <div class="bg-red-50 p-6 rounded-lg shadow-sm text-center">
            <p class="text-sm font-medium text-red-800">Belum Mengisi</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $belumMengisi }}</p>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tingkat Partisipasi ({{ $tingkatPartisipasi }}%)</h3>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-blue-500 h-4 rounded-full" style="width: {{ $tingkatPartisipasi }}%"></div>
        </div>
    </div>

    {{-- Tabel Daftar Peserta --}}
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold text-gray-800">Daftar Peserta</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">NIM</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participants as $user)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-3 px-4">{{ $user->email }}</td>
                            <td class="py-3 px-4">{{ $user->nim ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @if ($user->submission_status)
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">Sudah Mengisi</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-1 rounded-full">Belum Mengisi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-gray-500">Belum ada peserta di kelompok ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
