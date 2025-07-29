@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kuesioner Program Master</h1>
            <p class="text-gray-600">
                Program: <span class="font-semibold">{{ $program->name }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.program.questionnaires.sync', $program->id) }}" method="POST" onsubmit="return confirm('Yakin ingin sinkronkan kuesioner ke semua kelompok?')">
                @csrf
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-600">
                    🔄 Sinkron ke Semua Kelompok
                </button>
            </form>
            <a href="{{ route('admin.sertifikasi.index') }}" class="text-gray-500 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </div>

    <div class="mb-8 border-b pb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Tambah Pertanyaan Baru</h2>
        
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Terjadi Kesalahan</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('admin.program.questionnaires.store', $program->id) }}" method="POST">
            @csrf
            <div class="flex items-center gap-4">
                <input type="text" name="question" placeholder="Tulis pertanyaan di sini..." required class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                <select name="type" class="px-3 py-2 border border-gray-300 rounded-md">
                    <option value="yes_no">Ya / Tidak</option>
                    <option value="text">Saran (Teks)</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md font-semibold hover:bg-blue-700">Tambah</button>
            </div>
        </form>
    </div>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-700">Daftar Pertanyaan Program</h2>
            <p class="text-xs text-gray-500">Drag-and-drop untuk mengubah urutan pertanyaan. Perubahan akan mempengaruhi semua kelompok yang menggunakan program ini.</p>
        </div>
        <div>
            <button id="save-order-btn" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800">Simpan Urutan</button>
        </div>
    </div>

    <div id="question-list" class="space-y-4">
        @forelse ($questions as $question)
            <div draggable="true" data-id="{{ $question->id }}" class="bg-gray-50 p-4 rounded-md cursor-move border">
                <div class="flex items-center justify-between">
                    <p class="text-gray-800">{{ $question->question }}</p>
                    <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded-full">{{ $question->type == 'yes_no' ? 'Ya/Tidak' : 'Teks' }}</span>
                </div>
                <div class="text-right mt-2">
                    <form action="{{ route('admin.program.questionnaires.destroy', ['program' => $program->id, 'questionnaire' => $question->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pertanyaan ini?')" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Belum ada pertanyaan untuk program ini.</p>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('question-list');
    let draggedItem = null;

    list.addEventListener('dragstart', e => {
        draggedItem = e.target;
        setTimeout(() => {
            e.target.style.opacity = '0.5';
        }, 0);
    });

    list.addEventListener('dragend', e => {
        setTimeout(() => {
            draggedItem.style.opacity = '1';
            draggedItem = null;
        }, 0);
    });

    list.addEventListener('dragover', e => {
        e.preventDefault();
        const afterElement = getDragAfterElement(list, e.clientY);
        if (afterElement == null) {
            list.appendChild(draggedItem);
        } else {
            list.insertBefore(draggedItem, afterElement);
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('[draggable="true"]:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    const saveBtn = document.getElementById('save-order-btn');
    saveBtn.addEventListener('click', () => {
        const orderedIds = [...list.querySelectorAll('[data-id]')].map(item => item.dataset.id);
        
        fetch('{{ route("admin.program.questionnaires.updateOrder", $program->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order: orderedIds })
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert('Urutan berhasil disimpan!');
                saveBtn.classList.add('bg-green-500');
                setTimeout(() => saveBtn.classList.remove('bg-green-500'), 2000);
            } else {
                alert('Gagal menyimpan urutan.');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>
@endsection 