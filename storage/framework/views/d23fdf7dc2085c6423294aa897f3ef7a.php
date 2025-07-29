

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Selamat Datang, <?php echo e(Auth::user()->name); ?>!</h1>
            <p class="text-gray-600 mt-1">Anda login sebagai: <span class="font-semibold"><?php echo e(ucfirst(Auth::user()->role)); ?></span></p>
        </div>
    </div>

    
    <?php if(Auth::user()->role === 'proctor'): ?>
    <div class="mb-6">
        <a href="<?php echo e(route('proctor.ketercapaian')); ?>" class="inline-block bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-1.621-1.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h4.5M12 3v12" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Laporan Ketercapaian Pengawasan</h3>
                    <p class="text-sm text-gray-500">Lihat rekap performa semua grup yang Anda awasi.</p>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

    
    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-700">Daftar Tugas Kegiatan Anda</h2>

        <?php if(session('error')): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white p-5 rounded-lg shadow-sm flex items-center justify-between">
                
                <div class="flex items-center gap-4">
                    <img src="<?php echo e(asset($group->activity->logo)); ?>" alt="Logo Kegiatan" class="h-12 w-12 object-contain rounded-md bg-gray-100 p-1">
                    <div>
                        <p class="font-bold text-gray-800"><?php echo e($group->activity->name); ?></p>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">Kelompok:</span> <?php echo e($group->name); ?> |
                            <span class="font-semibold">Trainer:</span> <?php echo e(optional($group->trainers->first())->name ?? 'N/A'); ?>

                        </p>
                    </div>
                </div>

                
                <div class="flex items-center gap-2">
                    <?php if(Auth::user()->role === 'trainer'): ?>
                        <?php
                            $availableQuestions = $group->questionnaires->where('type', '!=', 'text');
                            $answeredQuestions = Auth::user()->answers()
                                ->whereIn('questionnaire_id', $availableQuestions->pluck('id'))
                                ->where('training_group_id', $group->id)
                                ->get();
                            $hasAnsweredAll = $availableQuestions->count() > 0 && $availableQuestions->count() === $answeredQuestions->count();
                        ?>
                        
                        <a href="<?php echo e(route('trainer.results', $group->id)); ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg text-sm">Lihat Hasil & Saran</a>
                        
                        <?php if($availableQuestions->count() > 0): ?>
                            <?php if($hasAnsweredAll): ?>
                                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Telah Diisi
                                </span>
                            <?php else: ?>
                                <a href="<?php echo e(route('groups.evaluasi.form', $group->id)); ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Isi Kuesioner
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if(Auth::user()->role === 'proctor'): ?>
                        <?php
                            $availableQuestions = $group->questionnaires->where('type', '!=', 'text');
                            $answeredQuestions = Auth::user()->answers()
                                ->whereIn('questionnaire_id', $availableQuestions->pluck('id'))
                                ->where('training_group_id', $group->id)
                                ->get();
                            $hasAnsweredAll = $availableQuestions->count() > 0 && $availableQuestions->count() === $answeredQuestions->count();
                        ?>
                        
                        <a href="<?php echo e(route('proctor.monitoring', $group->id)); ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg text-sm">Halaman Pengawasan</a>
                        
                        <?php if($availableQuestions->count() > 0): ?>
                            <?php if($hasAnsweredAll): ?>
                                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Telah Diisi
                                </span>
                            <?php else: ?>
                                <a href="<?php echo e(route('groups.evaluasi.form', $group->id)); ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Isi Kuesioner
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if(Auth::user()->role === 'user'): ?>
                        <?php if($group->submission_status): ?>
                            <button disabled class="bg-gray-200 text-gray-500 font-bold py-2 px-4 rounded-lg cursor-not-allowed text-sm">✔ Telah Diisi</button>
                        <?php else: ?>
                            <a href="<?php echo e(route('groups.evaluasi.form', $group->id)); ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg text-sm">
                                Isi Evaluasi
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white p-6 rounded-lg shadow-sm text-center text-gray-500">
                <p>Anda belum terdaftar di kelompok training manapun.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/home.blade.php ENDPATH**/ ?>