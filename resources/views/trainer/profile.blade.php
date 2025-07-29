@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        <div class="flex items-center gap-6">
            <img src="{{ $trainer->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($trainer->name).'&size=128' }}" alt="Avatar" class="h-24 w-24 rounded-full object-cover ring-4 ring-blue-100">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $trainer->name }}</h1>
                <p class="text-gray-500">{{ $trainer->email }}</p>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-semibold">Role:</span> {{ ucfirst($trainer->role) }}
                </p>
            </div>
        </div>
        <a href="{{ route('home.user') }}" class="text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Rasio Ketercapaian per Program</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($achievementByProgram as $program => $percentage)
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm border-l-4 {{ $percentage >= 80 ? 'border-green-500' : ($percentage >= 60 ? 'border-blue-500' : ($percentage >= 40 ? 'border-yellow-500' : 'border-red-500')) }}">
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $program }}</h3>
                        <div class="text-4xl font-bold {{ $percentage >= 80 ? 'text-green-600' : ($percentage >= 60 ? 'text-blue-600' : ($percentage >= 40 ? 'text-yellow-600' : 'text-red-600')) }}">
                            {{ $percentage }}%
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Rata-rata ketercapaian</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 md:col-span-3">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-gray-500">Anda belum memiliki rekap performa program.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
