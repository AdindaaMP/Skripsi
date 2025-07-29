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

    {{-- Chart Persentase Validasi Per Peran & Total Persentase Per Kelompok --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Chart Persentase Validasi Per Peran & Total Persentase Per Kelompok</h2>
        <div class="bg-gray-50 p-6 rounded-lg flex flex-row gap-8 items-center justify-between">
            <div class="flex-1 min-w-0" style="height: 180px;">
                <canvas id="roleAchievementChart"></canvas>
            </div>
            <div style="width: 200px; height: 200px;">
                <canvas id="totalAchievementPieChart"></canvas>
                <div id="pieStatusText" class="mt-1 px-2 py-1 rounded border flex items-center justify-center text-xs font-normal bg-gray-100 border-gray-300"></div>
            </div>
        </div>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart.js DataLabels plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Chart Ketercapaian Per Role
    const roleLabels = ['Trainer', 'Proctor', 'Peserta'];
    const roleData = [
        {{ $trainer_percentage ?? 0 }},
        {{ $proctor_percentage ?? 0 }},
        {{ $peserta_percentage ?? 0 }}
    ];
    const roleColors = ['#2563eb', '#a21caf', '#f59e42']; // biru, ungu, oranye
    const roleCtx = document.getElementById('roleAchievementChart').getContext('2d');
    new Chart(roleCtx, {
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

    // Pie Chart Rasio Total Ketercapaian
    const finalScore = {{ $final_score ?? 0 }};
    const pieData = [finalScore, 100 - finalScore];
    const pieColors = ['#2563eb', '#e5e7eb'];
    const pieLabels = ['Tercapai', 'Belum Tercapai'];
    const pieCtx = document.getElementById('totalAchievementPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: pieColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                datalabels: {
                    display: false // Nonaktifkan label di dalam pie chart
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Perbaiki tampilan keterangan di bawah pie chart
    var statusText = '';
    var statusColor = '';
    var dotColor = '';
    if (finalScore >= 70) {
        statusText = '<span class="text-gray-700">' + finalScore + '% - </span><span class="text-green-600 font-semibold">Tercapai</span>';
        statusColor = '';
        dotColor = '#22c55e';
    } else {
        statusText = '<span class="text-gray-700">' + finalScore + '% - </span><span class="text-red-600 font-semibold">Belum Tercapai</span>';
        statusColor = '';
        dotColor = '#ef4444';
    }
    var statusDiv = document.getElementById('pieStatusText');
    statusDiv.className = 'mt-1 px-2 py-1 rounded border flex items-center justify-center text-xs font-normal bg-gray-100 border-gray-300';
    statusDiv.innerHTML = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + dotColor + ';margin-right:6px;"></span>' + statusText;
});
</script>
