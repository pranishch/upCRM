<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Helpers\ActivityLogger;
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

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::whereRaw('BINARY email = ?', [$identifier])->first();
        } else {
            $user = User::whereRaw('BINARY username = ?', [$identifier])->first();
        }
        
        if ($user && Hash::check($password, $user->password)) {
            if ($user->is_active) {
                Auth::login($user);
                $request->session()->regenerate();
                
                // Log successful login
                ActivityLogger::log('User logged in', [
                    'identifier' => $identifier,
                    'ip_address' => $request->ip(),
                ]);

                $role = $user->userProfile ? $user->userProfile->role : ($user->is_superuser ? 'admin' : 'agent');

                if ($role === 'admin' || $user->is_superuser) {
                    return redirect()->route('admin_dashboard');
                } elseif ($role === 'manager') {
                    return redirect()->route('manager_dashboard', ['manager_id' => $user->id]);
                } else {
                    return redirect()->route('callbacklist');
                }
            } else {
                // Log failed login attempt (inactive account)
                ActivityLogger::log('Failed login attempt', [
                    'identifier' => $identifier,
                    'reason' => 'Inactive account',
                    'ip_address' => $request->ip(),
                ]);
                return back()->withErrors(['error' => 'Your account is inactive. Please contact administrator.']);
            }
        }

        // Log failed login attempt (invalid credentials)
        ActivityLogger::log('Failed login attempt', [
            'identifier' => $identifier,
            'reason' => 'Invalid credentials',
            'ip_address' => $request->ip(),
        ]);
        return back()->withErrors(['error' => 'Invalid username/email or password.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        // Log logout action
        ActivityLogger::log('User logged out', [
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}