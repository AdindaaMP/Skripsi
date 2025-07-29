@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-7xl mx-auto">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Hasil Evaluasi - {{ $group->name }}</h1>
            <p class="text-gray-600">Hanya menampilkan pengguna yang sudah mengisi kuesioner.</p>
        </div>
        <a href="{{ route('admin.kuesioner.index', ['group' => $group->id]) }}" class="text-gray-500 hover:text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    </div>

    {{-- Form untuk Filter dan Search --}}
    <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg mb-6">
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-4">
            <input type="text" name="search" placeholder="Cari nama atau email..." value="{{ request('search') }}" class="border px-3 py-1.5 rounded-lg text-sm w-64">
            <button type="submit" class="bg-blue-500 text-white px-5 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-600">Terapkan</button>
            <a href="{{ route('admin.kuesioner.results', $group->id) }}" class="text-sm text-gray-600 hover:underline">Reset Filter</a>
        </form>
        
        {{-- Tombol Download CSV --}}
        <a href="{{ route('admin.kuesioner.export', ['group' => $group->id, 'search' => request('search')]) }}" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-600">
            Download Hasil (CSV)
        </a>
    </div>

    {{-- Tabel Hasil Evaluasi --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="py-3 px-4 sticky left-0 bg-gray-50 z-10">Nama Pengisi</th>
                    <th class="py-3 px-4 border-l">Role</th>
                    @foreach ($questions as $question)
                        <th class="py-3 px-4 border-l">{{ Str::limit($question->question, 30) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($members as $member)
                    <tr>
                        <td class="py-3 px-4 sticky left-0 bg-white font-medium text-gray-900">{{ $member->name }}</td>
                        <td class="py-3 px-4 border-l">
                            <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full
                                @if($member->role == 'trainer') bg-green-100 text-green-700
                                @elseif($member->role == 'proctor') bg-yellow-100 text-yellow-700
                                @else bg-blue-100 text-blue-700 @endif">
                                {{ ucfirst($member->role) }}
                            </span>
                        </td>
                        @foreach ($questions as $question)
                            <td class="py-3 px-4 border-l">
                                @php
                                    $answer = $answersByMember[$member->id][$question->id] ?? null;
                                @endphp

                                @if ($answer)
                                    @if($question->type == 'yes_no')
                                        <span class="{{ $answer->value ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                            {{ $answer->value ? 'Ya' : 'Tidak' }}
                                        </span>
                                    @else
                                        <p class="text-xs italic text-gray-500" title="{{ $answer->value }}">{{ Str::limit($answer->value, 40) }}</p> 
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $questions->count() + 2 }}" class="text-center py-6 text-gray-500">
                            Tidak ada data yang cocok dengan filter atau pencarian Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
