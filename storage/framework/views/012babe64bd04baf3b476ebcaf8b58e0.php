
<?php $__env->startSection('content'); ?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-3xl mx-auto">
    
    
    <div class="border-b pb-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Formulir Evaluasi</h1>
        <p class="text-gray-600">
            Untuk Kelompok: <span class="font-semibold"><?php echo e($group->name); ?></span> 
            dalam kegiatan <?php echo e($group->activity->name); ?>

            <?php if($group->program): ?>
                <br>Program: <span class="font-semibold"><?php echo e($group->program->name); ?></span>
            <?php endif; ?>
        </p>
    </div>

    
    <?php if(session('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>
    
    
    <?php if($hasAnsweredAll): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
            <p class="font-bold">Terima Kasih</p>
            <p>Anda sudah menyelesaikan evaluasi untuk kelompok ini. Jawaban Anda sudah terekam.</p>
        </div>
    <?php endif; ?>

    
    <form action="<?php echo e(route('groups.evaluasi.submit', $group->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="space-y-8">
            <?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <fieldset class="border p-4 rounded-md">
                    <legend class="px-2 font-medium text-gray-800">Pertanyaan <?php echo e($index + 1); ?></legend>
                    <p class="mb-3 text-gray-700"><?php echo e($question->question); ?></p>
                    
                    <?php
                        $previousAnswer = $userAnswers->get($question->id);
                    ?>

                    
                    <?php if($question->type === 'yes_no'): ?>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="answers[<?php echo e($question->id); ?>][value]" value="1" 
                                       <?php echo e(optional($previousAnswer)->value == 1 ? 'checked' : ''); ?> 
                                       <?php echo e($hasAnsweredAll ? 'disabled' : ''); ?>

                                       required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span>Ya</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="answers[<?php echo e($question->id); ?>][value]" value="0"
                                       <?php echo e((optional($previousAnswer)->value !== null && optional($previousAnswer)->value == 0) ? 'checked' : ''); ?>

                                       <?php echo e($hasAnsweredAll ? 'disabled' : ''); ?>

                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span>Tidak</span>
                            </label>
                        </div>
                    <?php elseif($question->type === 'text'): ?>
                        <textarea name="answers[<?php echo e($question->id); ?>][value]" rows="3" 
                                  placeholder="Tuliskan saran atau masukan Anda di sini..."
                                  <?php echo e($hasAnsweredAll ? 'disabled' : ''); ?>

                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo e(optional($previousAnswer)->value ?? ''); ?></textarea>
                    <?php endif; ?>
                    
                </fieldset>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-gray-500">Belum ada pertanyaan evaluasi yang disiapkan untuk kelompok ini.</p>
            <?php endif; ?>
        </div>

        
        <?php if($questions->isNotEmpty() && !$hasAnsweredAll): ?>
            <div class="flex justify-end mt-8 pt-5 border-t">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">
                    Kirim Evaluasi
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/evaluasi/form.blade.php ENDPATH**/ ?>