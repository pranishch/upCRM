<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CanAddCallbacks
{
    public function handle(Request $request, Closure $next)
    {
        $targetUserId = $request->input('user_id') ?? Auth::id();
        $targetUser = User::find($targetUserId);

        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        if (Auth::user()->is_superuser || (Auth::user()->userProfile && Auth::user()->userProfile->role === 'admin')) {
            abort(403, 'Admins cannot add callbacks.');
        }

        if (Auth::user()->id === $targetUser->id) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}