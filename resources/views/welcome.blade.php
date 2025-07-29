<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Evaluasi Ketercapaian Materi Sertifikasi</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen font-sans">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-center items-center">
            <img src="{{ asset('assets/Logo_ITCC_Lanscape.png') }}" alt="Logo" class="h-16 w-auto">
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <!-- Judul Halaman -->
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                SISTEM EVALUASI KETERCAPAIAN MATERI SERTIFIKASI
            </h1>
        </div>

        @if (session('error'))
            <div class="max-w-md mx-auto bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="max-w-md mx-auto bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Login Cards -->
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Peserta Card -->
                <div class="bg-white rounded-xl shadow-2xl hover:shadow-3xl transition-all duration-700 ease-out transform hover:-translate-y-6 border border-gray-100">
                    <div class="p-8 text-center relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent rounded-xl opacity-50"></div>
                        <div class="relative z-10">
                            <div class="mb-4">
                                <img src="{{ asset('assets/logo-no_text.png') }}" alt="Logo" class="w-16 h-16 mx-auto drop-shadow-lg transition-transform duration-500 ease-out hover:scale-110">
                            </div>
                            <h3 class="text-3xl font-bold text-gray-800 mb-8 transition-all duration-300 ease-out">Peserta</h3>
                            <a href="{{ route('login.microsoft', ['role' => 'user']) }}" 
                               class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-500 ease-out w-full shadow-lg hover:shadow-xl transform hover:scale-105">
                                Masuk sebagai Peserta
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Trainer Card -->
                <div class="bg-white rounded-xl shadow-2xl hover:shadow-3xl transition-all duration-700 ease-out transform hover:-translate-y-6 border border-gray-100">
                    <div class="p-8 text-center relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent rounded-xl opacity-50"></div>
                        <div class="relative z-10">
                            <div class="mb-4">
                                <img src="{{ asset('assets/logo-no_text.png') }}" alt="Logo" class="w-16 h-16 mx-auto drop-shadow-lg transition-transform duration-500 ease-out hover:scale-110">
                            </div>
                            <h3 class="text-3xl font-bold text-gray-800 mb-8 transition-all duration-300 ease-out">Trainer</h3>
                            <a href="{{ route('login.microsoft', ['role' => 'trainer']) }}" 
                               class="inline-block bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-4 rounded-xl font-semibold hover:from-green-700 hover:to-green-800 transition-all duration-500 ease-out w-full shadow-lg hover:shadow-xl transform hover:scale-105">
                                Masuk sebagai Trainer
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Proctor Card -->
                <div class="bg-white rounded-xl shadow-2xl hover:shadow-3xl transition-all duration-700 ease-out transform hover:-translate-y-6 border border-gray-100">
                    <div class="p-8 text-center relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-transparent rounded-xl opacity-50"></div>
                        <div class="relative z-10">
                            <div class="mb-4">
                                <img src="{{ asset('assets/logo-no_text.png') }}" alt="Logo" class="w-16 h-16 mx-auto drop-shadow-lg transition-transform duration-500 ease-out hover:scale-110">
                            </div>
                            <h3 class="text-3xl font-bold text-gray-800 mb-8 transition-all duration-300 ease-out">Proctor</h3>
                            <a href="{{ route('login.microsoft', ['role' => 'proctor']) }}" 
                               class="inline-block bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-4 rounded-xl font-semibold hover:from-purple-700 hover:to-purple-800 transition-all duration-500 ease-out w-full shadow-lg hover:shadow-xl transform hover:scale-105">
                                Masuk sebagai Proctor
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="mt-12 bg-white rounded-xl shadow-lg p-8">
                <div class="text-center">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Tentang Sistem</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Sistem Evaluasi Ketercapaian Materi Sertifikasi adalah platform terintegrasi yang memungkinkan 
                        peserta, trainer, dan proctor untuk berkolaborasi dalam proses evaluasi pembelajaran. 
                        Sistem ini dirancang untuk memantau dan mengukur tingkat ketercapaian materi pelatihan 
                        dengan akurat dan transparan.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white mt-16 py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-600">
                © 2024 ITCC ITPN. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
