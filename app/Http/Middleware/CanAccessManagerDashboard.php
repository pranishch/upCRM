<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CanAccessManagerDashboard
{
    public function handle(Request $request, Closure $next)
    {
        $managerId = $request->route('manager_id');
        $manager = User::find($managerId);

        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        if (Auth::user()->is_superuser || (Auth::user()->userProfile && Auth::user()->userProfile->role === 'admin')) {
            return $next($request);
        }

        if (Auth::user()->id === $manager->id) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
