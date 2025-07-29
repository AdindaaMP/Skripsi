<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans flex h-screen <?php echo e(($sidebarPosition ?? 'left') === 'left' ? '' : 'flex-row-reverse'); ?>">

    <aside class="w-64 bg-white shadow-md flex-shrink-0 flex flex-col justify-between">
        <div>
            <div class="h-16 flex items-center justify-center border-b">
                <img src="<?php echo e(asset('assets/Logo_ITCC_Lanscape.png')); ?>" alt="Logo ITCC" class="h-10">
            </div>
            <!-- Menu Navigasi (Disesuaikan dengan Role) -->
            <nav class="mt-4 px-4 space-y-1">
                
                
                <a href="<?php echo e(Auth::user()->role === 'admin' ? route('admin.dashboard') : route('home.user')); ?>" 
                class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                    <span class="material-icons">home</span>
                    <span>Dashboard</span>
                </a>
                
                <?php if(Auth::user()->role === 'trainer'): ?>
                    <a href="<?php echo e(route('trainer.profile')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                        <span class="material-icons">bar_chart</span>
                        <span>Profil & Performa Saya</span>
                    </a>
                <?php endif; ?>
                
                <?php if(Auth::user()->role === 'proctor'): ?>
                <a href="<?php echo e(route('proctor.ketercapaian')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                    <span class="material-icons">assessment</span>
                    <span>Laporan Pengawasan</span>
                </a>
                <?php endif; ?>

                
                <?php if(Auth::user()->role === 'admin'): ?>
                    <a href="<?php echo e(route('admin.sertifikasi.index')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                        <span class="material-icons">assignment</span>
                        <span>Sertifikasi</span>
                    </a>
                    <a href="<?php echo e(route('admin.trainer.index')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                        <span class="material-icons">person</span>
                        <span>Trainer</span>
                    </a>
                    <a href="<?php echo e(route('admin.proctor.index')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                        <span class="material-icons">people</span>
                        <span>Proctor</span>
                    </a>
                    <a href="<?php echo e(route('admin.user.index')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                        <span class="material-icons">account_circle</span>
                        <span>User</span>
                    </a>
                    <a href="<?php echo e(route('admin.administrator.index')); ?>" class="flex items-center space-x-3 rounded-lg hover:bg-gray-100 p-3 text-gray-700 transition-colors duration-200">
                        <span class="material-icons">settings</span>
                        <span>Administrator</span>
                    </a>
                <?php endif; ?> 
            </nav>
        </div>
                <div class="p-4 border-t">
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex items-center space-x-3 text-gray-600 hover:text-gray-800 p-3 rounded-lg hover:bg-gray-100 w-full">
                    <span class="material-icons">exit_to_app</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-6">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

</body>
</html><?php /**PATH C:\xampp\htdocs\kakdin\resources\views/layouts/admin.blade.php ENDPATH**/ ?>