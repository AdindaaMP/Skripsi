@extends('layouts.admin')

@section('content')
<header class="mb-4">
    <h1 class="text-2xl font-bold">Dashboard Admin</h1>
</header>

<form method="GET" class="mb-6">
    <label for="year" class="mr-2 font-medium">Filter Tahun:</label>
    <select name="year" id="year" onchange="this.form.submit()" class="border p-2 rounded">
        <option value="">-- Semua Tahun --</option>
        @foreach ($availableYears as $year)
            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
        @endforeach
    </select>
</form>

{{-- Tabel Trainer dengan Ketercapaian per Program --}}
<div class="bg-white p-6 rounded shadow mb-6">
    <h2 class="text-xl font-bold mb-4">Ketercapaian Trainer per Program</h2>
    
    @if(!empty($trainerAchievements))
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Nama Trainer
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Program
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Ketercapaian (%)
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($trainerAchievements as $achievement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $achievement['trainer_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $achievement['program_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold text-lg {{ $achievement['average_percentage'] >= 90 ? 'text-green-600' : ($achievement['average_percentage'] >= 70 ? 'text-blue-600' : ($achievement['average_percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600')) }}">
                                    {{ $achievement['average_percentage'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($achievement['average_percentage'] >= 90)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sangat Baik</span>
                                @elseif($achievement['average_percentage'] >= 70)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Baik</span>
                                @elseif($achievement['average_percentage'] >= 60)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Cukup</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Perlu Perbaikan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-sm text-gray-600">
            <p><strong>Total Trainer:</strong> {{ count($trainerAchievements) }}</p>
            <p><strong>Rata-rata Ketercapaian:</strong> {{ round(collect($trainerAchievements)->avg('average_percentage')) }}%</p>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="text-gray-500">Belum ada data ketercapaian trainer untuk ditampilkan.</p>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="dashboard-container">
    @forelse ($activitySummaries as $index => $activity)
        <div class="bg-white p-6 rounded shadow hover:shadow-lg transition">
            <h2 class="text-xl font-bold mb-4">{{ $activity['activity_name'] }}</h2>

            <a href="{{ route('admin.activities.exportXlsx', $activity['activity_id']) }}"
               class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 inline-block mb-4 ml-2">
                📊 Export ke XLSX
            </a>

            @if (count($activity['groups']) > 0)
                <a href="{{ route('admin.sertifikasi.show', $activity['activity_id']) }}">
                    <canvas id="chart-{{ $index }}" height="120" class="mb-4 cursor-pointer hover:opacity-80"></canvas>
                </a>
            @else
                <div class="h-[120px] flex items-center justify-center bg-gray-50 rounded-md mb-4">
                    <p class="text-gray-500">Belum ada kelompok di kegiatan ini.</p>
                </div>
            @endif

            <div class="text-right">
                <a href="{{ route('admin.sertifikasi.show', $activity['activity_id']) }}"
                   class="bg-blue-500 text-white rounded px-3 py-1 hover:bg-blue-600 text-sm">
                    🔍 Detail
                </a>
            </div>
        </div>
    @empty
        <div class="md:col-span-2 bg-white p-6 rounded shadow text-center text-gray-500">
            <p>Tidak ada kegiatan untuk ditampilkan.</p>
        </div>
    @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const activityData = @json($activitySummaries);
            const colors = [
                'rgba(59, 130, 246, 0.6)', 'rgba(16, 185, 129, 0.6)',
                'rgba(244, 114, 182, 0.6)', 'rgba(99, 102, 241, 0.6)',
                'rgba(234, 179, 8, 0.6)', 'rgba(168, 85, 247, 0.6)',
            ];
            activityData.forEach((activity, index) => {
                const chartElement = document.getElementById(`chart-${index}`);
                if (!chartElement) return;
                const ctx = chartElement.getContext('2d');
                const labels = activity.groups.map(g => g.group_name);
                // PASTIKAN chart mengambil dari field 'percentage' hasil pembobotan
                const percentages = activity.groups.map(g => g.percentage);
                const chartColors = labels.map((_, i) => colors[i % colors.length]);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ketercapaian (%)',
                            data: percentages,
                            backgroundColor: chartColors,
                            borderColor: chartColors.map(c => c.replace('0.6', '1')),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        // Tooltip hanya tampilkan nilai yang sama dengan tabel
                                        return `${context.label}: ${context.raw}%`;
                                    }
                                }
                            },
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true, max: 100,
                                ticks: { callback: value => value + '%' }
                            }
                        },
                        onClick: function (evt, elements) {
                            if (elements.length > 0) {
                                window.location.href = `/admin/sertifikasi/${activity.activity_id}`;
                            }
                        }
                    }
                });
            });
        } catch (e) {
            console.error("Gagal memproses data atau membuat chart:", e);
            const container = document.getElementById('dashboard-container');
            if(container) {
                container.innerHTML = '<div class="md:col-span-2 bg-red-100 p-6 rounded shadow text-red-700">Terjadi kesalahan saat memuat data untuk chart. Silakan periksa console browser (F12) untuk detailnya.</div>';
            }
        }
    });
</script>
@endsection
