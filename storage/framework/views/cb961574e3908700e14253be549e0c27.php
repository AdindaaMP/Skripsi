

<?php $__env->startSection('content'); ?>
<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Ubah Kelompok</h1>
    <p class="text-gray-600 mb-6">untuk kegiatan: <span class="font-semibold"><?php echo e($sertifikasi->name); ?></span></p>

    <?php if($errors->any()): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Terjadi Kesalahan</p>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Error</p>
            <p><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Berhasil!</p>
            <p><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.sertifikasi.groups.update', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id])); ?>" method="POST" id="edit-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kelompok</label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name', $group->name)); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="kuota" class="block text-sm font-medium text-gray-700 mb-1">Kuota Peserta</label>
                <input type="number" name="kuota" id="kuota" value="<?php echo e(old('kuota', $group->kuota)); ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div>
                <label for="program_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Program</label>
                <select name="program_id" id="program_id" required class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">-- Pilih Program --</option>
                    <?php $__currentLoopData = \App\Models\Program::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($program->id); ?>" <?php echo e((old('program_id', $group->program_id) == $program->id) ? 'selected' : ''); ?>>
                            <?php echo e($program->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label for="trainer_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Trainer</label>
                <select id="trainer_id" name="trainer_id" class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Tidak Ada Trainer</option>
                    <?php $__currentLoopData = $trainers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trainer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($trainer->id); ?>" <?php echo e($group->trainers->contains($trainer->id) ? 'selected' : ''); ?>><?php echo e($trainer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="proctor_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Proctor</label>
                <select id="proctor_id" name="proctor_id" class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Tidak Ada Proctor</option> 
                    <?php $__currentLoopData = $proctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($proctor->id); ?>" <?php echo e($group->proctors->contains($proctor->id) ? 'selected' : ''); ?>><?php echo e($proctor->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
    </form>

    <div class="flex justify-between items-center mt-8 pt-5 border-t">
        <div>
            <form action="<?php echo e(route('admin.sertifikasi.groups.destroy', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id])); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok ini? Semua data terkait akan hilang permanen.');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">
                    Hapus Kelompok
                </button>
            </form>
        </div>
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.sertifikasi.groups.show', ['sertifikasi' => $sertifikasi->id, 'group' => $group->id])); ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">Batal</a>
            <button type="submit" form="edit-form" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/admin/training_groups/edit.blade.php ENDPATH**/ ?>