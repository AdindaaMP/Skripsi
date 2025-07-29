

<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
    <a href="<?php echo e(route('admin.user.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600">
        Tambah User Baru
    </a>
</div>


<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <form method="GET" action="<?php echo e(route('admin.user.index')); ?>" class="flex items-center gap-4">
        <input type="text" name="search" placeholder="Cari nama atau email..." value="<?php echo e(request('search')); ?>" class="border px-3 py-2 rounded-lg text-sm w-64">
        <select name="role" class="border px-3 py-2 rounded-lg text-sm">
            <option value="">Semua Peran</option>
            <option value="admin" <?php echo e(request('role') == 'admin' ? 'selected' : ''); ?>>Admin</option>
            <option value="trainer" <?php echo e(request('role') == 'trainer' ? 'selected' : ''); ?>>Trainer</option>
            <option value="proctor" <?php echo e(request('role') == 'proctor' ? 'selected' : ''); ?>>Proctor</option>
            <option value="user" <?php echo e(request('role') == 'user' ? 'selected' : ''); ?>>Peserta</option>
        </select>
        <button type="submit" class="bg-gray-700 text-white px-5 py-2 rounded-lg text-sm font-semibold">Terapkan</button>
        <a href="<?php echo e(route('admin.user.index')); ?>" class="text-sm text-gray-600 hover:underline">Reset</a>
    </form>
</div>


<div class="bg-white p-6 rounded-lg shadow-sm">
    <table class="w-full text-sm text-left text-gray-600">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="py-3 px-4">Nama</th>
                <th class="py-3 px-4">Email</th>
                <th class="py-3 px-4">Peran</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 flex items-center gap-3">
                        <img src="<?php echo e($user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name)); ?>" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                        <span class="font-medium text-gray-900"><?php echo e($user->name); ?></span>
                    </td>
                    <td class="py-3 px-4"><?php echo e($user->email); ?></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full
                            <?php switch($user->role):
                                case ('admin'): ?> bg-red-100 text-red-700 <?php break; ?>
                                <?php case ('trainer'): ?> bg-green-100 text-green-700 <?php break; ?>
                                <?php case ('proctor'): ?> bg-yellow-100 text-yellow-700 <?php break; ?>
                                <?php default: ?> bg-blue-100 text-blue-700
                            <?php endswitch; ?>">
                            <?php echo e(ucfirst($user->role)); ?>

                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <a href="<?php echo e(route('admin.user.edit', $user->id)); ?>" class="bg-cyan-500 text-white px-4 py-1 rounded-full text-xs font-semibold hover:bg-cyan-600">Ubah</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center py-4">Tidak ada pengguna yang cocok dengan kriteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="mt-6">
        <?php echo e($users->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/admin/user/index.blade.php ENDPATH**/ ?>