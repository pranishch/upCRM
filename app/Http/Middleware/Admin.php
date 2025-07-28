<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        dd('Admin middleware reached');
        $user = Auth::user();

        if ($user && ($user->is_superuser || ($user->userProfile && $user->userProfile->role === 'admin'))) {
            return $next($request);
        }

        return redirect()->route('login')->withErrors(['error' => 'Unauthorized access. Admin privileges required.']);
    }
}