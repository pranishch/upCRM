<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CanManageUsers
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->is_superuser || (Auth::user()->userProfile && Auth::user()->userProfile->role === 'admin')) {
            return $next($request);
        }
        abort(403, 'Unauthorized action.');
    }
}
