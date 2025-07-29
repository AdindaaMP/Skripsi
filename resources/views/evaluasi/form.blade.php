@extends('layouts.admin')
@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-3xl mx-auto">
    
    {{-- Header Halaman --}}
    <div class="border-b pb-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Formulir Evaluasi</h1>
        <p class="text-gray-600">
            Untuk Kelompok: <span class="font-semibold">{{ $group->name }}</span> 
            dalam kegiatan {{ $group->activity->name }}
            @if($group->program)
                <br>Program: <span class="font-semibold">{{ $group->program->name }}</span>
            @endif
        </p>
    </div>

    {{-- Pesan setelah berhasil submit --}}
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    
    {{-- Pesan jika sudah pernah mengisi semua --}}
    @if ($hasAnsweredAll)
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
            <p class="font-bold">Terima Kasih</p>
            <p>Anda sudah menyelesaikan evaluasi untuk kelompok ini. Jawaban Anda sudah terekam.</p>
        </div>
    @endif

    {{-- Form Evaluasi --}}
    <form action="{{ route('groups.evaluasi.submit', $group->id) }}" method="POST">
        @csrf
        <div class="space-y-8">
            @forelse ($questions as $index => $question)
                <fieldset class="border p-4 rounded-md">
                    <legend class="px-2 font-medium text-gray-800">Pertanyaan {{ $index + 1 }}</legend>
                    <p class="mb-3 text-gray-700">{{ $question->question }}</p>
                    
                    @php
                        $previousAnswer = $userAnswers->get($question->id);
                    @endphp

                    {{-- Tampilkan input berdasarkan tipe pertanyaan --}}
                    @if ($question->type === 'yes_no')
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="answers[{{ $question->id }}][value]" value="1" 
                                       {{ optional($previousAnswer)->value == 1 ? 'checked' : '' }} 
                                       {{ $hasAnsweredAll ? 'disabled' : '' }}
                                       required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span>Ya</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="answers[{{ $question->id }}][value]" value="0"
                                       {{ (optional($previousAnswer)->value !== null && optional($previousAnswer)->value == 0) ? 'checked' : '' }}
                                       {{ $hasAnsweredAll ? 'disabled' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span>Tidak</span>
                            </label>
                        </div>
                    @elseif ($question->type === 'text')
                        <textarea name="answers[{{ $question->id }}][value]" rows="3" 
                                  placeholder="Tuliskan saran atau masukan Anda di sini..."
                                  {{ $hasAnsweredAll ? 'disabled' : '' }}
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ optional($previousAnswer)->value ?? '' }}</textarea>
                    @endif
                    
                </fieldset>
            @empty
                <p class="text-gray-500">Belum ada pertanyaan evaluasi yang disiapkan untuk kelompok ini.</p>
            @endforelse
        </div>

        {{-- Tampilkan tombol Kirim hanya jika ada pertanyaan dan belum diisi semua --}}
        @if($questions->isNotEmpty() && !$hasAnsweredAll)
            <div class="flex justify-end mt-8 pt-5 border-t">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">
                    Kirim Evaluasi
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
