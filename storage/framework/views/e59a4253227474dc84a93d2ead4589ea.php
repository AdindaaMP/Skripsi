

<?php $__env->startSection('content'); ?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    
    <div class="flex justify-between items-start mb-6 border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Ketercapaian Pengawasan</h1>
            <p class="text-gray-600">Rekapitulasi performa untuk semua grup yang Anda awasi.</p>
        </div>
        <a href="<?php echo e(route('home.user')); ?>" class="text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    </div>

    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Rasio Ketercapaian Grup yang Diawasi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $achievementByProgram; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $percentage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $border = $percentage >= 90 ? 'border-green-500' : ($percentage >= 70 ? 'border-blue-500' : ($percentage >= 60 ? 'border-yellow-500' : 'border-red-500'));
                    $text = $percentage >= 90 ? 'text-green-600' : ($percentage >= 70 ? 'text-blue-600' : ($percentage >= 60 ? 'text-yellow-600' : 'text-red-600'));
                    $icon = $percentage >= 90 ? 'check-circle' : 'info';
                ?>
                <div class="bg-gray-50 p-6 rounded-lg text-center shadow-sm border-l-4 <?php echo e($border); ?>">
                    <div class="mb-3">
                        <?php if($percentage >= 90): ?>
                            <svg class="w-8 h-8 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php else: ?>
                            <svg class="w-8 h-8 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo e($program); ?></h3>
                    <div class="text-4xl font-bold <?php echo e($text); ?>">
                        <?php echo e($percentage); ?>%
                    </div>
                    <div class="mt-1 text-sm font-medium text-gray-700">
                        <?php if($percentage >= 90): ?>
                            Sangat Baik
                        <?php elseif($percentage >= 70): ?>
                            Baik
                        <?php elseif($percentage >= 60): ?>
                            Cukup
                        <?php else: ?>
                            Perlu Perbaikan
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-8 md:col-span-3">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-gray-500">Anda belum memiliki rekap performa ketercapaian.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/proctor/ketercapaian.blade.php ENDPATH**/ ?>