<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!$this->isAdminUser(Auth::user())) {
                return redirect()->route('callbacklist')->with('error', 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    protected function isAdminUser($user)
    {
        return $user && (
            ($user->userProfile && $user->userProfile->role === 'admin') || 
            $user->is_superuser
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $users = User::whereHas('userProfile', function (Builder $query) {
            $query->whereIn('role', ['agent', 'manager']);
        })->orWhereDoesntHave('userProfile')
            ->where('is_superuser', false)
            ->with('userProfile')
            ->get();

        $total_users = User::count();
        $roles = ['agent', 'manager'];
        $callbacks = Callback::whereIn('created_by', $users->pluck('id'))
            ->orderBy('added_at', 'desc')
            ->get();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'viewed_users_list',
            'details' => json_encode([
                'username' => $user->username,
                'total_users' => $total_users,
            ]),
        ]);
        Log::info("Manage users viewed by {$user->username}");

        return response()->view('manage_users', compact('users', 'total_users', 'roles', 'callbacks'))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($request->input('action') === 'create') {
            // Log incoming request data for debugging
            \Log::info('UserController::store - Request data: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username|min:3|max:255',
                'email' => 'nullable|email|unique:users,email|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'password' => 'required|confirmed|min:8',
                'role' => 'required|in:agent,manager',
            ]);

            if ($validator->fails()) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'create_user_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'errors' => $validator->errors()->all(),
                    ]),
                ]);
                \Log::error('UserController::store - Validation failed: ' . json_encode($validator->errors()->all()));
                return redirect()->route('users.index')
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation failed. Please check the form inputs.');
            }

            DB::beginTransaction();

            try {
                // Create user
                $newUser = User::create([
                    'username' => trim($request->username),
                    'email' => $request->email ? trim($request->email) : null,
                    'first_name' => $request->first_name ? trim($request->first_name) : null,
                    'last_name' => $request->last_name ? trim($request->last_name) : null,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                    'is_superuser' => false,
                ]);

                \Log::info('UserController::store - User created: ' . $newUser->id);

                // Create user profile
                UserProfile::create([
                    'user_id' => $newUser->id,
                    'role' => $request->role,
                ]);

                \Log::info('UserController::store - UserProfile created for user: ' . $newUser->id);

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'created_user',
                    'details' => json_encode([
                        'username' => $user->username,
                        'new_user_id' => $newUser->id,
                        'new_user_username' => $newUser->username,
                        'role' => $request->role,
                    ]),
                ]);
                Log::info("User {$newUser->username} created by {$user->username} with role {$request->role}");
                DB::commit();
                return redirect()->route('users.index')
                    ->with('success', "User {$newUser->username} created successfully with role {$request->role}!");

            } catch (\Exception $e) {
                DB::rollback();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'create_user_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'error' => $e->getMessage(),
                    ]),
                ]);
                \Log::error('UserController::store - Error creating user: ' . $e->getMessage());
                return redirect()->route('users.index')
                    ->with('error', 'Failed to create user: ' . $e->getMessage());
            }
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'create_user_failed',
            'details' => json_encode([
                'username' => $user->username,
                'error' => 'Invalid action: ' . $request->input('action'),
            ]),
        ]);
        \Log::error('UserController::store - Invalid action: ' . $request->input('action'));
        return redirect()->route('users.index')->with('error', 'Invalid action.');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($request->input('action') === 'edit') {
            $editUser = User::findOrFail($request->user_id);

            // Prevent self-editing unless superuser
            if ($editUser->id === Auth::id() && !Auth::user()->is_superuser) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'update_user_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'edit_user_id' => $editUser->id,
                        'error' => 'Cannot edit own details',
                    ]),
                ]);
                Log::warning("User {$user->username} attempted to edit own user details");
                return redirect()->route('users.index')
                    ->with('error', 'You cannot edit your own details.');
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username,' . $editUser->id . '|min:3|max:255',
                'email' => 'nullable|email|unique:users,email,' . $editUser->id . '|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'update_user_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'edit_user_id' => $editUser->id,
                        'errors' => $validator->errors()->all(),
                    ]),
                ]);
                return redirect()->route('users.index')
                    ->withErrors($validator)
                    ->withInput();
            }
            
            DB::beginTransaction();
            try {
                $oldValues = $editUser->only(['username', 'email', 'first_name', 'last_name']);

                $editUser->update([
                    'username' => trim($request->username),
                    'email' => $request->email ? trim($request->email) : null,
                    'first_name' => $request->first_name ? trim($request->first_name) : null,
                    'last_name' => $request->last_name ? trim($request->last_name) : null,
                ]);

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'updated_user',
                    'details' => json_encode([
                        'username' => $user->username,
                        'edit_user_id' => $editUser->id,
                        'old_values' => $oldValues,
                        'new_values' => $request->only(['username', 'email', 'first_name', 'last_name']),
                    ]),
                ]);
                Log::info("User {$editUser->username} updated by {$user->username}");
                DB::commit();
                return redirect()->route('users.index')
                    ->with('success', "User {$editUser->username} updated successfully!");

            } catch (\Exception $e) {
                DB::rollback();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'update_user_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'edit_user_id' => $request->user_id,
                        'error' => $e->getMessage(),
                    ]),
                ]);
                \Log::error('Error updating user: ' . $e->getMessage());
                
                return redirect()->route('users.index')
                    ->with('error', 'Failed to update user. Please try again.');
            }
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update_user_failed',
            'details' => json_encode([
                'username' => $user->username,
                'error' => 'Invalid action: ' . $request->input('action'),
            ]),
        ]);
        return redirect()->route('users.index')->with('error', 'Invalid action.');
    }

    public function changeRole(Request $request)
    {
        $user = Auth::user();
        if ($request->input('action') === 'change_role') {
            $changeUser = User::findOrFail($request->user_id);

            // Prevent self role change unless superuser
            if ($changeUser->id === Auth::id() && !Auth::user()->is_superuser) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'change_user_role_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'change_user_id' => $changeUser->id,
                        'error' => 'Cannot change own role',
                    ]),
                ]);
                Log::warning("User {$user->username} attempted to change own user role");
                return redirect()->route('users.index')
                    ->with('error', 'You cannot change your own role.');
            }

            $validator = Validator::make($request->all(), [
                'new_role' => 'required|in:agent,manager',
            ]);

            if ($validator->fails()) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'change_user_role_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'change_user_id' => $changeUser->id,
                        'errors' => $validator->errors()->all(),
                    ]),
                ]);
                return redirect()->route('users.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();
            try {

                $new_role = $request->new_role;
                $old_role = $changeUser->userProfile ? $changeUser->userProfile->role : 'none';
                
                // Update or create profile
                $profile = $changeUser->userProfile;
                if ($profile) {
                    $profile->update(['role' => $new_role]);
                } else {
                    UserProfile::create([
                        'user_id' => $changeUser->id,
                        'role' => $new_role
                    ]);
                }

                
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'changed_user_role',
                    'details' => json_encode([
                        'username' => $user->username,
                        'change_user_id' => $changeUser->id,
                        'change_user_username' => $changeUser->username,
                        'old_role' => $old_role,
                        'new_role' => $new_role,
                    ]),
                ]);
                Log::info("User {$changeUser->username} role changed to {$new_role} by {$user->username}");               
                DB::commit();
                return redirect()->route('users.index')
                    ->with('success', "User {$changeUser->username} role changed to {$new_role}.");

            } catch (\Exception $e) {
                DB::rollBack();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'change_user_role_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'change_user_id' => $request->user_id,
                        'error' => $e->getMessage(),
                    ]),
                ]);
                \Log::error('Error changing user role: ' . $e->getMessage());
                
                return redirect()->route('users.index')
                    ->with('error', 'Failed to change role. Please try again.');
            }
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'change_user_role_failed',
            'details' => json_encode([
                'username' => $user->username,
                'error' => 'Invalid action: ' . $request->input('action'),
            ]),
        ]);
        return redirect()->route('users.index')->with('error', 'Invalid action.');
    }
    public function resetPassword(Request $request)
    {
        $user = Auth::user();
        if ($request->input('action') === 'reset_password') {
            if (!$this->isAdminUser(Auth::user())) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'reset_user_password_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'error' => 'Access denied. Admin privileges required.',
                    ]),
                ]);
                Log::error("User {$user->username} attempted to reset user password without admin privileges");
                return redirect()->route('users.index')
                    ->with('error', 'Access denied. Admin privileges required to reset passwords.');
            }

            $resetUser = User::findOrFail($request->user_id);

            // Prevent self-password reset unless superuser
            if ($resetUser->id === Auth::id() && !Auth::user()->is_superuser) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'reset_user_password_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'reset_user_id' => $resetUser->id,
                        'error' => 'Cannot reset own password',
                    ]),
                ]);
                Log::warning("User {$user->username} attempted to reset own user password");
                return redirect()->route('users.index')
                    ->with('error', 'You cannot reset your own password.');
            }

            $validator = Validator::make($request->all(), [
                'new_password' => 'required|confirmed|min:8',
            ]);

            if ($validator->fails()) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'reset_user_password_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'reset_user_id' => $resetUser->id,
                        'errors' => $validator->errors()->all(),
                    ]),
                ]);
                return redirect()->route('users.index')
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation failed. Please check the password inputs.');
            }
            DB::beginTransaction();
            try {
                $resetUser->update([
                    'password' => Hash::make($request->new_password),
                ]);

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'reset_user_password',
                    'details' => json_encode([
                        'username' => $user->username,
                        'reset_user_id' => $resetUser->id,
                        'reset_user_username' => $resetUser->username,
                    ]),
                ]);
                \Log::info('UserController::resetPassword - Password reset for user: ' . $resetUser->id);
                DB::commit();
                return redirect()->route('users.index')
                    ->with('success', "Password for user {$resetUser->username} reset successfully!");

            } catch (\Exception $e) {
                DB::rollback();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'reset_user_password_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'reset_user_id' => $request->user_id,
                        'error' => $e->getMessage(),
                    ]),
                ]);
                \Log::error('UserController::resetPassword - Error resetting password: ' . $e->getMessage());
                return redirect()->route('users.index')
                    ->with('error', 'Failed to reset password: ' . $e->getMessage());
            }
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'reset_user_password_failed',
            'details' => json_encode([
                'username' => $user->username,
                'error' => 'Invalid action: ' . $request->input('action'),
            ]),
        ]);
        return redirect()->route('users.index')->with('error', 'Invalid action.');
    }


    public function destroy($id)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $deleteUser = User::findOrFail($id);
            $username = $deleteUser->username;
            
            // Prevent self-deletion
            if ($deleteUser->id === Auth::id()) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'delete_user_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'delete_user_id' => $deleteUser->id,
                        'error' => 'Cannot delete own account',
                    ]),
                ]);
                Log::warning("User {$user->username} attempted to delete own user account");
                return redirect()->route('users.index')
                    ->with('error', 'You cannot delete your own account.');
            }

            // Delete user profile first (if exists)
            if ($deleteUser->userProfile) {
                $deleteUser->userProfile->delete();
            }

            $deleteUser->delete();

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'deleted_user',
                'details' => json_encode([
                    'username' => $user->username,
                    'deleted_user_id' => $id,
                    'deleted_user_username' => $username,
                ]),
            ]);
            Log::info("User {$username} deleted by {$user->username}");
            DB::commit();
            return redirect()->route('users.index')
                ->with('success', "User {$username} deleted successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'delete_user_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'delete_user_id' => $id,
                    'error' => $e->getMessage(),
                ]),
            ]);
            \Log::error('Error deleting user: ' . $e->getMessage());
            
            return redirect()->route('users.index')
                ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    public function updateCallback(Request $request)
    {
        $user = Auth::user();
        if ($request->input('action') === 'edit_callback') {
            if (!$this->isAdminUser(Auth::user())) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'update_user_callback_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'callback_id' => $request->callback_id,
                        'error' => 'Access denied. Admin privileges required.',
                    ]),
                ]);
                Log::error("User {$user->username} attempted to update user callback without admin privileges");
                return redirect()->route('users.index')
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
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'update_user_callback_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'callback_id' => $callback->id,
                        'errors' => $validator->errors()->all(),
                    ]),
                ]);
                return redirect()->route('users.index')
                    ->withErrors($validator)
                    ->withInput();
            }
            DB::beginTransaction();
            try {
                $oldValues = $callback->only([
                    'customer_name', 'phone_number', 'email', 'address', 'website', 'remarks', 'notes', 'added_at'
                ]);

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

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'updated_user_callback',
                    'details' => json_encode([
                        'username' => $user->username,
                        'callback_id' => $callback->id,
                        'old_values' => $oldValues,
                        'new_values' => $request->only([
                            'customer_name', 'phone_number', 'email', 'address', 'website', 'remarks', 'notes', 'added_at'
                        ]),
                    ]),
                ]);
                Log::info("User callback {$callback->id} updated by {$user->username}");
                DB::commit();
                return redirect()->route('users.index')
                    ->with('success', "Callback {$callback->id} updated successfully!");

            } catch (\Exception $e) {
                DB::rollback();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'update_user_callback_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'callback_id' => $request->callback_id,
                        'error' => $e->getMessage(),
                    ]),
                ]);
                \Log::error('Error updating callback: ' . $e->getMessage());
                
                return redirect()->route('users.index')
                    ->with('error', 'Failed to update callback. Please try again.');
            }
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update_user_callback_failed',
            'details' => json_encode([
                'username' => $user->username,
                'error' => 'Invalid action: ' . $request->input('action'),
            ]),
        ]);
        return redirect()->route('users.index')->with('error', 'Invalid action.');
    }
}