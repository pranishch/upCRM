<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class CanEditAllCallbacks
{
    public function handle(Request $request, Closure $next)
    {
        $role = Auth::user()->userProfile ? Auth::user()->userProfile->role : 'agent';
        if (in_array($role, ['admin', 'manager'])) {
            return $next($request);
        }
        abort(403, 'Unauthorized action.');
    }
}
