<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $identifier = $request->input('email');
        $password = $request->input('password');

        // Check if identifier contains @ to determine if it's email or username
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // It's an email - keep case-insensitive for emails (standard practice)
            $user = User::whereRaw('BINARY email = ?', [$identifier])->first();
        } else {
            // It's a username - make it case-sensitive using BINARY
            $user = User::whereRaw('BINARY username = ?', [$identifier])->first();
        }
        
        if ($user && Hash::check($password, $user->password)) {
            if ($user->is_active) {
                Auth::login($user);
                $request->session()->regenerate();

                $role = $user->userProfile ? $user->userProfile->role : ($user->is_superuser ? 'admin' : 'agent');

                if ($role === 'admin' || $user->is_superuser) {
                    return redirect()->route('admin_dashboard'); // Changed from admin.dashboard to admin_dashboard
                } elseif ($role === 'manager') {
                    return redirect()->route('manager_dashboard', ['manager_id' => $user->id]);
                } else {
                    return redirect()->route('callbacklist');
                }
            } else {
                return back()->withErrors(['error' => 'Your account is inactive. Please contact administrator.']);
            }
        }

        return back()->withErrors(['error' => 'Invalid username/email or password.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
?>