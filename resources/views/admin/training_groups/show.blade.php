@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-7xl mx-auto">
    
    {{-- Header Halaman --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $group->name }}</h1>
            <p class="text-gray-600">
                Bagian dari kegiatan: 
                <a href="{{ route('admin.sertifikasi.show', $activity->id) }}" class="text-blue-500 hover:underline">{{ $activity->name }}</a>
            </p>
        </div>
        <a href="{{ route('admin.sertifikasi.show', $activity->id) }}" class="text-gray-500 hover:text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    {{-- Info Group dan Chart --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="md:col-span-2 bg-gray-50 p-6 rounded-lg flex flex-col gap-6">
            <div>
                <h3 class="font-semibold text-lg text-gray-800 mb-4">Total Peserta: {{ $group->users->count() }}</h3>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase">
                        <tr>
                            <th class="pb-2">Proctor</th>
                            <th class="pb-2">Trainer</th>
                            <th class="pb-2">Program</th>
                            <th class="pb-2">Ketercapaian(%)</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="pt-2">{{ optional($group->proctors->first())->name ?? '-' }}</td>
                            <td class="pt-2">{{ optional($group->trainers->first())->name ?? '-' }}</td>
                            <td class="py-2">{{ optional($group->program)->name ?? 'Program tidak diatur' }}</td>
                            <td class="pt-2 font-bold text-lg text-blue-600">{{ $percentage }}%</td>
                            <td class="pt-2">
                                @if($percentage >= 60)
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">Tercapai</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">Belum Tercapai</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <div class="text-center font-semibold mb-2">Chart Persentase Validasi Per Peran</div>
                <div class="w-full max-w-md mx-auto"><canvas id="weightedBarChart"></canvas></div>
            </div>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg flex flex-col items-center justify-between">
            <h3 class="font-semibold text-lg text-gray-800 mb-2">Pie Chart Pengisi Kuesioner</h3>
            <div class="w-32 h-32"><canvas id="submissionRoleChart"></canvas></div>
            <a href="{{ route('admin.kuesioner.index', $group->id) }}" class="w-full text-center bg-gray-800 text-white mt-4 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700">
                Ubah Kuesioner
            </a>
            @if($group->program && $group->program_id)
                <a href="{{ route('admin.program.questionnaires.index', $group->program_id) }}" class="w-full text-center bg-blue-600 text-white mt-2 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                    Kelola Kuesioner {{ $group->program->name }}
                </a>
            @else
                <div class="w-full text-center bg-gray-400 text-white mt-2 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                    Program Kuesioner (Tidak Tersedia)
                </div>
            @endif
        </div>
    </div>

    {{-- Tabel Peserta --}}
    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Training</h2>
            <div class="flex items-center gap-4">
                <form method="GET" action="{{ url()->current() }}" class="flex gap-2">
                    <input type="text" name="search" placeholder="Cari nama, email, atau nim..." value="{{ request('search') }}" class="border px-3 py-1.5 rounded-lg text-sm w-64">
                    <button type="submit" class="bg-gray-200 px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-gray-300">Cari</button>
                </form>
                <a href="{{ route('admin.sertifikasi.groups.edit', ['sertifikasi' => $activity->id, 'group' => $group->id]) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-600">Ubah Kelompok</a>
                <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700">Tambah Peserta</button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">NIM</th>
                        <th class="py-3 px-4">Jurusan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participants as $user)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $user->email }}</td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-3 px-4">{{ $user->nim ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $user->jurusan ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @if ($user->submission_status)
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">Sudah Mengisi</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-1 rounded-full">Belum Mengisi</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <form action="{{ route('admin.groups.removeUser', ['group' => $group->id, 'user' => $user->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus peserta ini dari kelompok?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-gray-500">Tidak ada peserta yang cocok dengan pencarian.</td></tr>
                    @endforelse
                    @foreach ($invitedEmails as $invite)
                        <tr class="border-b bg-yellow-50">
                            <td class="py-3 px-4">{{ $invite->email }}</td>
                            <td class="py-3 px-4 font-medium text-gray-900">-</td>
                            <td class="py-3 px-4">-</td>
                            <td class="py-3 px-4">-</td>
                            <td class="py-3 px-4"><span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-1 rounded-full">Undangan Terkirim</span></td>
                            <td class="py-3 px-4 text-center">-</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Peserta dengan Pencarian dan Email Manual --}}
<div id="addUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Tambah Peserta ke Kelompok</h3>
                <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="mt-2 px-7 py-3">
                <form id="manual-invite-form" action="{{ route('admin.groups.inviteUser', $group->id) }}" method="POST" class="mb-4">
                    @csrf
                    <input type="email" name="email" placeholder="Masukkan email peserta @itpln.ac.id" required class="w-full border rounded-md p-2 mb-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold w-full">Undang Email Peserta</button>
                </form>
                <hr class="my-4">
                <input type="text" id="user-search-input" placeholder="Ketik nama, email, atau NIM..." class="w-full border rounded-md p-2 mb-4">
                <div id="user-search-results" class="space-y-2 max-h-60 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart.js DataLabels plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const submissionCtx = document.getElementById('submissionRoleChart')?.getContext('2d');
        if (submissionCtx) {
            new Chart(submissionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Peserta', 'Trainer', 'Proctor'],
                    datasets: [{
                        data: [
                            {{ $pesertaSudahMengisi }},
                            {{ $trainerSudahMengisi }},
                            {{ $proctorSudahMengisi }}
                        ],
                        backgroundColor: ['#3B82F6', '#10B981', '#EF4444'],
                        borderColor: '#ffffff',
                        borderWidth: 4,
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${ctx.raw} orang`
                            }
                        }
                    }
                }
            });
        }

        // CHART BATANG PEMBOBOTAN KETERISIAN PER ROLE
        const weightedBarCtx = document.getElementById('weightedBarChart')?.getContext('2d');
        if (weightedBarCtx) {
            const proctor_percentage = {{ $proctor_percentage ?? 0 }};
            const trainer_percentage = {{ $trainer_percentage ?? 0 }};
            const peserta_percentage = {{ $peserta_percentage ?? 0 }};
            const roleLabels = ['Trainer', 'Proctor', 'Peserta'];
            const roleData = [trainer_percentage, proctor_percentage, peserta_percentage];
            const roleColors = ['#2563eb', '#a21caf', '#f59e42']; // biru, ungu, oranye
            new Chart(weightedBarCtx, {
                type: 'bar',
                data: {
                    labels: roleLabels,
                    datasets: [{
                        label: 'Ketercapaian (%)',
                        data: roleData,
                        backgroundColor: roleColors,
                        borderRadius: 8,
                        maxBarThickness: 60,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    }]
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'top',
                            align: 'top',
                            offset: 4,
                            color: '#fff',
                            font: { weight: 'bold', size: 14 },
                            formatter: function(value) {
                                return value + '%';
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(val) { return val + '%'; },
                                color: '#6B7280'
                            },
                            grid: { color: 'rgba(156, 163, 175, 0.2)' }
                        },
                        x: {
                            ticks: { color: '#6B7280' },
                            grid: { display: false }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        // Script untuk pencarian peserta
        const searchInput = document.getElementById('user-search-input');
        const resultsContainer = document.getElementById('user-search-results');
        const groupId = {{ $group->id }};
        let searchTimeout;

        searchInput.addEventListener('keyup', function () {
            clearTimeout(searchTimeout);
            const query = this.value;

            if (query.length < 3) {
                resultsContainer.innerHTML = '<p class="text-xs text-gray-500">Ketik minimal 3 huruf untuk memulai pencarian.</p>';
                return;
            }

            resultsContainer.innerHTML = '<p class="text-xs text-gray-500">Mencari...</p>';

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('admin.users.search') }}?query=${query}&group_id=${groupId}`)
                    .then(response => response.json())
                    .then(users => {
                        resultsContainer.innerHTML = '';
                        if (users.length > 0) {
                            users.forEach(user => {
                                const userElement = document.createElement('div');
                                userElement.className = 'flex justify-between items-center p-2 border rounded-md';
                                userElement.innerHTML = `
                                    <div>
                                        <p class="font-semibold">${user.name}</p>
                                        <p class="text-xs text-gray-500">${user.email}</p>
                                    </div>
                                    <form action="{{ route('admin.groups.addUser', $group->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="user_id" value="${user.id}">
                                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 text-xs rounded-md">Tambah</button>
                                    </form>
                                `;
                                resultsContainer.appendChild(userElement);
                            });
                        } else {
                            resultsContainer.innerHTML = '<p class="text-xs text-gray-500">Tidak ada pengguna yang ditemukan.</p>';
                        }
                    });
            }, 500);
        });
    });
</script>
@endsection
