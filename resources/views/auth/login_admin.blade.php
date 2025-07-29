@extends('layouts.app')

@section('content')
<style>
    body {
        background: #f8fafc !important;
    }
    .login-card {
        animation: fadeInUp 0.7s cubic-bezier(0.23, 1, 0.32, 1);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
        border: 1px solid #e0e7ff;
        background: #fff;
        position: relative;
        z-index: 2;
    }
    .login-accent {
        position: absolute;
        left: 50%;
        bottom: -40px;
        transform: translateX(-50%);
        width: 340px;
        height: 80px;
        background: linear-gradient(90deg, #60a5fa 0%, #818cf8 100%);
        opacity: 0.18;
        border-radius: 50%;
        filter: blur(8px);
        z-index: 1;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .logo-animate:hover {
        transform: scale(1.08) rotate(-3deg);
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    }
    .ms-btn {
        background: linear-gradient(90deg, #2563eb 0%, #7c3aed 100%);
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
        letter-spacing: 0.02em;
        box-shadow: 0 4px 14px 0 rgba(60, 72, 180, 0.15);
        transition: all 0.2s;
        border: none;
    }
    .ms-btn:hover {
        background: linear-gradient(90deg, #1e40af 0%, #6d28d9 100%);
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 8px 24px 0 rgba(60, 72, 180, 0.18);
    }
</style>
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center mb-2">
            <img src="{{ asset('assets/logo-no_text.png') }}" alt="Logo" class="h-20 w-auto logo-animate transition-transform duration-300">
        </div>
        <h2 class="mt-2 text-center text-4xl font-extrabold text-gray-900 drop-shadow-lg tracking-tight">
            Login Admin
        </h2>
        <p class="mt-2 text-center text-base text-gray-700 font-medium">
            Masuk sebagai administrator menggunakan akun Microsoft
        </p>
    </div>
    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-md relative">
        <div class="login-card py-10 px-8 rounded-2xl">
            <a href="{{ route('login.microsoft', ['role' => 'admin']) }}"
               class="ms-btn w-full flex items-center justify-center gap-3 py-3 px-4 rounded-xl text-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-6 h-6" viewBox="0 0 32 32"><rect fill="#F35325" x="1" y="1" width="14" height="14"/><rect fill="#81BC06" x="17" y="1" width="14" height="14"/><rect fill="#05A6F0" x="1" y="17" width="14" height="14"/><rect fill="#FFBA08" x="17" y="17" width="14" height="14"/></svg>
                <span>Login dengan Microsoft</span>
            </a>
        </div>
        <div class="login-accent"></div>
    </div>
</div>
@endsection 