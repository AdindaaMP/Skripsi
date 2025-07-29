

<?php $__env->startSection('content'); ?>
   <header class="flex justify-between items-start mb-6">
    <div class="flex items-center space-x-4">
        <img src="<?php echo e(asset($activity->logo)); ?>" alt="Activity Logo" class="h-16 w-16 object-contain rounded-lg bg-gray-200 p-1">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?php echo e($activity->name); ?></h1>
            <p class="text-sm text-gray-600 mt-1"><?php echo e($activity->description); ?></p>
        </div>
    </div>
    <div>
        <a href="<?php echo e(route('admin.sertifikasi.edit', $activity->id)); ?>" class="bg-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-400">
            Ubah Kegiatan
        </a>
    </div>
</header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Jumlah Peserta: <?php echo e($totalParticipants); ?></h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="py-3 px-4">Nama Kelompok</th>
                            <th class="py-3 px-4">Program</th>
                            <th class="py-3 px-4">Trainer</th>
                            <th class="py-3 px-4">Proctor</th>
                            <th class="py-3 px-4">Ketercapaian</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b">
                                <td class="py-3 px-4 font-medium text-gray-900"><?php echo e($group['name']); ?></td>
                                <td class="py-3 px-4"><?php echo e(optional($group['program'])->name ?? 'Program tidak diatur'); ?></td>
                                <td class="py-3 px-4"><?php echo e(optional($group['trainers']->first())->name ?? '-'); ?></td>
                                <td class="py-3 px-4"><?php echo e(optional($group['proctors']->first())->name ?? '-'); ?></td>
                                <td class="py-3 px-4 font-bold text-blue-600">
                                    <?php echo e($group['percentage'] ?? 0); ?>%
                                </td>
                                <td class="py-3 px-4">
                                    <?php if(($group['percentage'] ?? 0) >= 60): ?>
                                        <span class="inline-block px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">Tercapai</span>
                                    <?php else: ?>
                                        <span class="inline-block px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">Belum Tercapai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center py-4">Tidak ada data kelompok untuk ditampilkan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-bold text-gray-800">Ketercapaian per Kelompok</h3>
            <div class="mt-4" style="height: 250px;">
                
                <canvas id="mainBarChart"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Daftar Kelompok Training</h2>
            <a href="<?php echo e(route('admin.sertifikasi.groups.create', $activity->id)); ?>" class="bg-cyan-500 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-cyan-600">
                Tambah Kelompok
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="py-3 px-4">Chart</th>
                        <th class="py-3 px-4">Nama Kelompok</th>
                        <th class="py-3 px-4">Trainer</th>
                        <th class="py-3 px-4">Proctor</th>
                        <th class="py-3 px-4">Kuota Peserta</th>
                        <th class="py-3 px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="py-4 px-4 w-20">
                                <canvas id="donut-chart-group-<?php echo e($group->id); ?>" width="40" height="40"></canvas>
                            </td>
                            <td class="py-4 px-4 font-medium text-gray-900"><?php echo e($group->name); ?></td>
                            <td class="py-4 px-4"><?php echo e(optional($group->trainers->first())->name ?? '-'); ?></td>
                            <td class="py-4 px-4"><?php echo e(optional($group->proctors->first())->name ?? '-'); ?></td>
                            <td class="py-4 px-4"><?php echo e($group->users_count); ?> / <?php echo e($group->kuota ?? 10); ?></td>
                            <td class="py-4 px-4">
                                <a href="<?php echo e(route('admin.sertifikasi.groups.show', ['sertifikasi' => $activity->id, 'group' => $group->id])); ?>" 
                                   class="bg-cyan-500 text-white px-4 py-1 rounded-full text-xs font-semibold hover:bg-cyan-600">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center py-4">Belum ada kelompok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php
        // Mapping chartGroupsData harus mengambil jumlah orang yang sudah mengisi per role untuk chart donut
        $chartGroupsData = $groups->map(function ($group) {
            return [
                'id' => $group['id'],
                'name' => $group['name'],
                'percentage' => $group['percentage'], // hasil pembobotan
                'trainer_name' => $group['trainer_name'],
                'users_count' => $group['users_count'],
                'trainers_count' => $group['trainers']->count(),
                'proctors_count' => $group['proctors']->count(),
                // Jumlah orang yang sudah mengisi per role
                'filled_users' => $group['filled_users'] ?? 0,
                'filled_trainers' => $group['filled_trainers'] ?? 0,
                'filled_proctors' => $group['filled_proctors'] ?? 0,
            ]; 
        });
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const groupsData = <?php echo json_encode($chartGroupsData, 15, 512) ?>;
        // Chart batang harus mengambil data dari field 'percentage' hasil pembobotan
        const mainBarCtx = document.getElementById('mainBarChart')?.getContext('2d');
        if(mainBarCtx) {
            const colorPalette = ['#3B82F6', '#10B981', '#EF4444', '#F59E0B', '#8B5CF6', '#EC4899'];
            new Chart(mainBarCtx, {
                type: 'bar', 
                data: { 
                    labels: groupsData.map(g => g.name), 
                    datasets: [{ 
                        label: 'Ketercapaian', 
                        data: groupsData.map(g => g.percentage), 
                        backgroundColor: groupsData.map((_, i) => colorPalette[i % colorPalette.length] + 'B3'), 
                        borderColor: groupsData.map((_, i) => colorPalette[i % colorPalette.length]), 
                        borderWidth: 1, 
                        borderRadius: 4 
                    }] 
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false }, 
                        tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw}%` } } 
                    },
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: value => value + '%' } } }
                }
            });
        }
        
        // --- GAMBAR CHART DONAT UNTUK SETIAP KELOMPOK ---
        groupsData.forEach((group) => {
            const donutCanvas = document.getElementById(`donut-chart-group-${group.id}`);
            if (!donutCanvas) return;

            const ctx = donutCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Peserta', 'Trainer', 'Proctor'],
                    datasets: [{
                        data: [
                            group.filled_users || 0,
                            group.filled_trainers || 0,
                            group.filled_proctors || 0
                        ],
                        backgroundColor: ['#3B82F6', '#10B981', '#EF4444'],
                        borderColor: '#ffffff',
                        borderWidth: 4
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
        });
    });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/admin/sertifikasi/show.blade.php ENDPATH**/ ?>