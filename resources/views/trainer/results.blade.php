@extends('layouts.admin')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    
    <div class="flex justify-between items-start mb-6 border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Hasil Evaluasi & Saran</h1>
            <p class="text-gray-600">Untuk Kelompok: <span class="font-semibold">{{ $group->name }}</span> ({{ $activity->name }})</p>
            @if($group->program)
                <p class="text-gray-600">Program: <span class="font-semibold">{{ $group->program->name }}</span></p>
            @endif
        </div>
        <a href="{{ route('home.user') }}" class="text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    </div>

    {{-- Chart Ketercapaian --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Ketercapaian Materi (Jawaban "Ya")</h2>
        <div class="bg-gray-50 p-6 rounded-lg">
            @if($questionStats->isNotEmpty())
                <div class="relative" style="height: {{ max(200, $questionStats->count() * 60) }}px;">
                    <canvas id="achievementChart"></canvas>
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-gray-500">Tidak ada data evaluasi "Ya/Tidak" untuk ditampilkan.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Chart Ketercapaian Per Role & Pie Chart Total --}}
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

    {{-- Daftar Saran --}}
   <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Saran dari Peserta</h2>
        <div class="space-y-3">
            @forelse ($suggestionsFromPeserta as $saran)
                <div class="border-l-4 border-blue-300 bg-blue-50 p-4 rounded-r-lg">
                    <p class="italic text-gray-700">"{{ $saran }}"</p>
                </div>
            @empty
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-500">Tidak ada saran yang diterima dari peserta.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Saran dari Proctor</h2>
        <div class="space-y-3">
            @forelse ($suggestionsFromProctor as $saran)
                <div class="border-l-4 border-indigo-300 bg-indigo-50 p-4 rounded-r-lg">
                    <p class="italic text-gray-700">"{{ $saran }}"</p>
                </div>
            @empty
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-500">Tidak ada saran yang diterima dari proctor.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM Content Loaded');
    
    const chartData = @json($questionStats->toArray() ?? []);
    console.log('Chart data received:', chartData);

    if (!Array.isArray(chartData) || chartData.length === 0) {
        console.warn('Data chart kosong atau tidak valid.');
        return;
    }

    const canvas = document.getElementById('achievementChart');
    if (!canvas) {
        console.error('Canvas element tidak ditemukan.');
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Canvas context tidak dapat dibuat.');
        return;
    }

    console.log('Canvas context created successfully');

    // Ambil label dan nilai dari data
    const labels = chartData.map(item => {
        let q = item.question || '';
        return q.length > 50 ? q.slice(0, 50) + '...' : q;
    });

    const data = chartData.map(item => item.percentage || 0);

    console.log('Labels:', labels);
    console.log('Data:', data);

    const backgroundColors = data.map(percentage => {
        if (percentage >= 80) return 'rgba(34,197,94,0.7)';     // Hijau
        if (percentage >= 60) return 'rgba(59,130,246,0.7)';     // Biru
        if (percentage >= 40) return 'rgba(245,158,11,0.7)';     // Oranye
        return 'rgba(239,68,68,0.7)';                             // Merah
    });

    const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

    try {
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ketercapaian (%)',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1,
                    borderRadius: 5,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(val) {
                                return val + '%';
                            },
                            color: '#6B7280'
                        },
                        grid: {
                            color: 'rgba(156, 163, 175, 0.2)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#6B7280'
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return chartData[index] && chartData[index].question ? chartData[index].question : '';
                            },
                            label: function(context) {
                                return 'Ketercapaian: ' + context.parsed.x + '%';
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        console.log('Chart created successfully');
    } catch (error) {
        console.error('Error creating chart:', error);
    }

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

    // Perkecil dan ubah warna keterangan status
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
@endsection
