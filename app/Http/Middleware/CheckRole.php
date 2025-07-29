<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        Log::info('ROLE MIDDLEWARE | Authenticated: ' . (Auth::check() ? 'yes' : 'no'));
        Log::info('ROLE MIDDLEWARE | User: ' . optional(Auth::user())->email);
        Log::info('ROLE MIDDLEWARE | User Role: ' . optional(Auth::user())->role);
        Log::info('ROLE MIDDLEWARE | Allowed roles: ' . implode(',', $roles));
        Log::info('ROLE MIDDLEWARE | Request URL: ' . $request->url());

        if (!Auth::check()) {
            Log::warning('ROLE MIDDLEWARE | User not authenticated, redirecting to welcome');
            return redirect()->route('welcome');
        }

        $user = Auth::user();
        $userRole = $user->role;

        foreach ($roles as $role) {
            if (strtolower(trim($userRole)) === strtolower(trim($role))) {
                Log::info("ROLE MIDDLEWARE | Access granted for user {$user->email} with role {$userRole}");
                return $next($request);
            }
        }

        Log::warning("ROLE MIDDLEWARE | Access denied for user {$user->email} with role {$userRole}. Required roles: " . implode(',', $roles));
        abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES KE HALAMAN INI. Role Anda: ' . $userRole . '. Role yang diperlukan: ' . implode(', ', $roles));
    }
}
