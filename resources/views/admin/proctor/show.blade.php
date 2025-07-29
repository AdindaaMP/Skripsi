@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    
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
    
    <div class="flex justify-between items-start mb-8">
        <div class="flex items-center gap-6">
            <img src="{{ $proctor->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($proctor->name).'&size=128' }}" alt="Avatar" class="h-24 w-24 rounded-full object-cover ring-4 ring-blue-100">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $proctor->name }}</h1>
                <p class="text-gray-500">{{ $proctor->email }}</p>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('admin.proctor.edit', $proctor->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-600">Ubah</a>
                    <form action="{{ route('admin.proctor.destroy', $proctor->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus proctor ini? Role akan dikembalikan menjadi user biasa.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-600">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.proctor.index') }}" class="text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Rasio Ketercapaian Grup yang Diawasi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($achievementByProgram as $program => $percentage)
                <div class="bg-gray-50 p-6 rounded-lg text-center shadow-sm border-l-4 {{ $percentage >= 80 ? 'border-green-500' : ($percentage >= 60 ? 'border-blue-500' : ($percentage >= 40 ? 'border-yellow-500' : 'border-red-500')) }}">
                    <svg class="w-8 h-8 mx-auto {{ $percentage >= 80 ? 'text-green-500' : ($percentage >= 60 ? 'text-blue-500' : ($percentage >= 40 ? 'text-yellow-500' : 'text-red-500')) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($percentage >= 80)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        @elseif($percentage >= 60)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        @elseif($percentage >= 40)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        @endif
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $program }}</h3>
                    <div class="text-4xl font-bold {{ $percentage >= 90 ? 'text-green-600' : ($percentage >= 70 ? 'text-blue-600' : ($percentage >= 60 ? 'text-yellow-600' : 'text-red-600')) }}">
                        {{ $percentage }}%
                    </div>
                    <div class="mt-1 text-sm font-medium text-gray-700">
                        @if($percentage >= 90)
                            Sangat Baik
                        @elseif($percentage >= 70)
                            Baik
                        @elseif($percentage >= 60)
                            Cukup
                        @else
                            Perlu Perbaikan
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 md:col-span-3">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-gray-500">Proctor ini belum mengawasi grup manapun.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
