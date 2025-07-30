<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Add admin check middleware
        $this->middleware(function ($request, $next) {
            if (!$this->isAdminUser(Auth::user())) {
                return redirect()->route('callbacklist')->with('error', 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $managers = User::whereHas('userProfile', function ($query) {
            $query->where('role', 'manager');
        })->with('userProfile')->get();

        $roles = ['agent', 'manager'];
        $callbacks = Callback::whereIn('created_by', $managers->pluck('id'))
            ->orderBy('added_at', 'desc')
            ->get();

        return response()->view('manage_managers', compact('managers', 'roles', 'callbacks'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function store(Request $request)
    {
        if ($request->input('action') === 'create') {
            // Log incoming request data for debugging
            \Log::info('ManagerController::store - Request data: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username|min:3|max:255',
                'email' => 'nullable|email|unique:users,email|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'password' => 'required|confirmed|min:8',
                'role' => 'required|in:manager',
            ]);

            if ($validator->fails()) {
                \Log::error('ManagerController::store - Validation failed: ' . json_encode($validator->errors()->all()));
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation failed. Please check the form inputs.');
            }

            try {
                // Create user
                $user = User::create([
                    'username' => trim($request->username),
                    'email' => $request->email ? trim($request->email) : null,
                    'first_name' => $request->first_name ? trim($request->first_name) : null,
                    'last_name' => $request->last_name ? trim($request->last_name) : null,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                    'is_superuser' => false,
                ]);

                \Log::info('ManagerController::store - User created: ' . $user->id);

                // Create user profile
                UserProfile::create([
                    'user_id' => $user->id,
                    'role' => $request->role,
                ]);

                \Log::info('ManagerController::store - UserProfile created for user: ' . $user->id);

                return redirect()->route('managers.index')
                    ->with('success', "Manager {$user->username} created successfully!");

            } catch (\Exception $e) {
                \Log::error('ManagerController::store - Error creating manager: ' . $e->getMessage());
                return redirect()->route('managers.index')
                    ->with('error', 'Failed to create manager: ' . $e->getMessage());
            }
        }

        \Log::error('ManagerController::store - Invalid action: ' . $request->input('action'));
        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function update(Request $request)
    {
        if ($request->input('action') === 'edit') {
            $user = User::findOrFail($request->user_id);

            // Prevent self-editing unless superuser
            if ($user->id === Auth::id() && !Auth::user()->is_superuser) {
                return redirect()->route('managers.index')
                    ->with('error', 'You cannot edit your own details.');
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username,' . $user->id . '|min:3|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id . '|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                $user->update([
                    'username' => trim($request->username),
                    'email' => $request->email ? trim($request->email) : null,
                    'first_name' => $request->first_name ? trim($request->first_name) : null,
                    'last_name' => $request->last_name ? trim($request->last_name) : null,
                ]);

                return redirect()->route('managers.index')
                    ->with('success', "Manager {$user->username} updated successfully!");

            } catch (\Exception $e) {
                \Log::error('Error updating manager: ' . $e->getMessage());
                
                return redirect()->route('managers.index')
                    ->with('error', 'Failed to update manager. Please try again.');
            }
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function changeRole(Request $request)
    {
        if ($request->input('action') === 'change_role') {
            $user = User::findOrFail($request->user_id);

            // Prevent self role change unless superuser
            if ($user->id === Auth::id() && !Auth::user()->is_superuser) {
                return redirect()->route('managers.index')
                    ->with('error', 'You cannot change your own role.');
            }

            $validator = Validator::make($request->all(), [
                'new_role' => 'required|in:agent,manager',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                DB::beginTransaction();

                $new_role = $request->new_role;
                
                // Update or create profile
                $profile = $user->userProfile;
                if ($profile) {
                    $profile->update(['role' => $new_role]);
                } else {
                    UserProfile::create([
                        'user_id' => $user->id,
                        'role' => $new_role
                    ]);
                }

                DB::commit();

                return redirect()->route('managers.index')
                    ->with('success', "Manager {$user->username} role changed to {$new_role}.");

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error changing manager role: ' . $e->getMessage());
                
                return redirect()->route('managers.index')
                    ->with('error', 'Failed to change role. Please try again.');
            }
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function resetPassword(Request $request)
    {
        if ($request->input('action') === 'reset_password') {
            $user = User::findOrFail($request->user_id);

            // Prevent self password reset unless superuser
            if ($user->id === Auth::id() && !Auth::user()->is_superuser) {
                return redirect()->route('managers.index')
                    ->with('error', 'You cannot reset your own password.');
            }

            $validator = Validator::make($request->all(), [
                'new_password' => 'required|min:8|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                $user->update([
                    'password' => Hash::make($request->new_password)
                ]);

                return redirect()->route('managers.index')
                    ->with('success', "Password reset for {$user->username}!");

            } catch (\Exception $e) {
                \Log::error('Error resetting manager password: ' . $e->getMessage());
                
                return redirect()->route('managers.index')
                    ->with('error', 'Failed to reset password. Please try again.');
            }
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $username = $user->username;
            
            // Prevent self-deletion
            if ($user->id === Auth::id()) {
                return redirect()->route('managers.index')
                    ->with('error', 'You cannot delete your own account.');
            }

            // Delete user profile first (if exists)
            if ($user->userProfile) {
                $user->userProfile->delete();
            }

            $user->delete();

            return redirect()->route('managers.index')
                ->with('success', "Manager {$username} deleted successfully!");

        } catch (\Exception $e) {
            \Log::error('Error deleting manager: ' . $e->getMessage());
            
            return redirect()->route('managers.index')
                ->with('error', 'Failed to delete manager. Please try again.');
        }
    }

    public function updateCallback(Request $request)
    {
        if ($request->input('action') === 'edit_callback') {
            if (!$this->isAdminUser(Auth::user())) {
                return redirect()->route('managers.index')
                    ->with('error', 'Access denied. Admin privileges required to edit callbacks.');
            }

            $callback = Callback::findOrFail($request->callback_id);

            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|regex:/^[A-Za-z\s]+$/|min:2|max:255',
                'phone_number' => 'required|regex:/^[\+\-0-9\s\(\),./#]+$/|min:5|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|min:5|max:500',
                'website' => 'nullable|url|max:255',
                'remarks' => 'nullable|max:255',
                'notes' => 'nullable|max:255',
                'added_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                $callback->update([
                    'customer_name' => $request->customer_name,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email ?: null,
                    'address' => $request->address ?: null,
                    'website' => $request->website ?: null,
                    'remarks' => $request->remarks ?: null,
                    'notes' => $request->notes ?: null,
                    'added_at' => $request->added_at ? Carbon::parse($request->added_at) : Carbon::now(),
                ]);

                return redirect()->route('managers.index')
                    ->with('success', "Callback {$callback->id} updated successfully!");

            } catch (\Exception $e) {
                \Log::error('Error updating callback: ' . $e->getMessage());
                
                return redirect()->route('managers.index')
                    ->with('error', 'Failed to update callback. Please try again.');
            }
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    protected function isAdminUser($user)
    {
        return $user && (
            ($user->userProfile && $user->userProfile->role === 'admin') || 
            $user->is_superuser
        );
    }
}