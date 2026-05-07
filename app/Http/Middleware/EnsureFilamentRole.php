<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFilamentRole
{
    private const INTERNAL_ROLES = [
        UserRole::SUPER_ADMIN,
        UserRole::SALES,
        UserRole::AUDITOR,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user || ! $user->is_active || ! in_array($user->role, self::INTERNAL_ROLES, true)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('filament.admin.auth.login');
        }

        return $next($request);
    }
}
