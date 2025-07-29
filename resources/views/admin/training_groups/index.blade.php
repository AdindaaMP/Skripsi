@extends('layouts.admin')

@section('content')
<h2 class="text-xl font-bold mb-4">Kelompok untuk: {{ $sertifikasi->name }}</h2>

<a href="{{ route('admin.sertifikasi.groups.create', $sertifikasi->id) }}"
   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Tambah Kelompok</a>

<ul class="mt-4 space-y-2">
    @foreach ($groups as $group)
        <li class="bg-white p-4 rounded shadow">
            {{ $group->name }} - Kuota: {{ $group->kuota ?? 'Tidak ditentukan' }}
        </li>
    @endforeach
</ul>
@endsection
