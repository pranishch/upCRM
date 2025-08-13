<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
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
        // Log dashboard view
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'viewed_admin_dashboard',
            'details' => json_encode([
                'username' => $user->username,
                'role' => $user->is_superuser ? 'admin' : ($user->userProfile ? $user->userProfile->role : 'user'),
            ]),
        ]);
        Log::info("Admin dashboard viewed by {$user->username}");

        // Ensure all users have a UserProfile
        DB::beginTransaction();
        try {
            User::whereDoesntHave('userProfile')->get()->each(function ($user) {
                UserProfile::create(['user_id' => $user->id, 'role' => 'agent']);
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'profile_creation_failed',
                'details' => json_encode(['error' => $e->getMessage()]),
            ]);
            Log::error("Failed to create user profiles: {$e->getMessage()}");
        }

        // Fetch users excluding admins and superusers
        $users = User::whereHas('userProfile', function ($query) {
            $query->whereIn('role', ['manager', 'agent']);
        })->with('userProfile')->get();

        $total_users = $users->count();
        $total_managers = User::whereHas('userProfile', function ($query) {
            $query->where('role', 'manager');
        })->count();
        
        $managers = User::whereHas('userProfile', function ($query) {
            $query->where('role', 'manager');
        })->with('userProfile')->get();

        $query = Callback::with(['createdBy.userProfile', 'manager.userProfile'])->orderBy('added_at', 'desc');
        $search_query = $request->query('q', '');
        $search_field = $request->query('search_field', 'all');

        if ($search_query) {
            try {
                if ($search_field == 'all') {
                    $query->where(function ($q) use ($search_query) {
                        $q->where('customer_name', 'like', "%{$search_query}%")
                          ->orWhere('phone_number', 'like', "%{$search_query}%")
                          ->orWhere('email', 'like', "%{$search_query}%")
                          ->orWhere('address', 'like', "%{$search_query}%")
                          ->orWhere('website', 'like', "%{$search_query}%")
                          ->orWhere('remarks', 'like', "%{$search_query}%")
                          ->orWhere('notes', 'like', "%{$search_query}%")
                          ->orWhereHas('createdBy', function ($q) use ($search_query) {
                              $q->where('username', 'like', "%{$search_query}%");
                          });
                    });
                } elseif ($search_field == 'customer_name') {
                    $query->where('customer_name', 'like', "%{$search_query}%");
                } elseif ($search_field == 'phone_number') {
                    $query->where('phone_number', 'like', "%{$search_query}%");
                } elseif ($search_field == 'email') {
                    $query->where('email', 'like', "%{$search_query}%");
                }
            } catch (\Exception $e) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'search_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'error' => 'Invalid search query',
                        'search_query' => $search_query,
                        'search_field' => $search_field,
                    ]),
                ]);
                Log::error("Search failed by {$user->username}: {$e->getMessage()}");
                return $request->ajax()
                    ? response()->json(['status' => 'error', 'message' => 'Invalid search query'], 400)
                    : redirect()->route('admin_dashboard')->with('error', 'An error occurred while processing the search query.');
            }
        }

        $total_callbacks = $query->count();
        $all_callbacks = $query->paginate(20);

        if ($request->ajax()) {
            $callbacks_html = view('admin_dashboard_table_body', [
                'all_callbacks' => $all_callbacks,
                'user_role' => 'admin',
                'managers' => $managers,
            ])->render();
            $pagination_html = view('admin_dashboard_pagination', [
                'page_obj' => $all_callbacks,
                'search_query' => $search_query,
                'search_field' => $search_field,
            ])->render();

            return response()->json([
                'status' => 'success',
                'callbacks_html' => $callbacks_html,
                'pagination_html' => $pagination_html,
                'total_callbacks' => $total_callbacks,
            ]);
        }

        return response()->view('admin_dashboard', [
            'users' => $users,
            'total_callbacks' => $total_callbacks,
            'total_users' => $total_users,
            'total_managers' => $total_managers,
            'all_callbacks' => $all_callbacks,
            'managers' => $managers,
            'user_role' => 'admin',
            'page_obj' => $all_callbacks,
            'search_query' => $search_query,
            'search_field' => $search_field,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    public function assignManager(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            // Validate the request
            $request->validate([
                'callback_id' => 'required|exists:callbacks,id',
                'manager_id' => 'nullable|exists:users,id',
            ]);

            // Find the callback
            $callback = Callback::findOrFail($request->callback_id);

            // Check if the selected manager is valid
            if ($request->manager_id) {
                $manager = User::findOrFail($request->manager_id);
                if (!$manager->userProfile || $manager->userProfile->role !== 'manager') {
                    throw new \Exception('Selected user is not a manager.');
                }
            }

            // Update the callback's manager_id
            $callback->manager_id = $request->manager_id ?: null;
            $callback->save();

            // Log success
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'assigned_manager',
                'details' => json_encode([
                    'username' => $user->username,
                    'callback_id' => $request->callback_id,
                    'manager_id' => $request->manager_id ?: null,
                    'manager_username' => $request->manager_id ? User::find($request->manager_id)->username : null,
                ]),
            ]);
            Log::info("Manager assigned to callback {$request->callback_id} by {$user->username}");

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Manager assigned successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'assign_manager_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'callback_id' => $request->callback_id,
                    'manager_id' => $request->manager_id ?: null,
                    'errors' => $e->errors(),
                ]),
            ]);
            Log::error("Validation error assigning manager: " . json_encode($e->errors()));
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'assign_manager_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'callback_id' => $request->callback_id,
                    'manager_id' => $request->manager_id ?: null,
                    'error' => $e->getMessage(),
                ]),
            ]);
            Log::error("Error assigning manager: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() === 'Selected user is not a manager.' ? $e->getMessage() : 'An error occurred while assigning the manager.',
            ], 400);
        }
    }

    public function updateCallback(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            // Validate the request
            $request->validate([
                'callback_id' => 'required|exists:callbacks,id',
                'customer_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
                'phone_number' => ['required', 'string', 'min:5', 'max:20', 'regex:/^\+?[0-9]{1,4}[\-\s]?[0-9]{1,4}[\-\s]?[0-9]{1,4}[\-\s]?[0-9]{1,4}$/'],
                'email' => ['nullable', 'email', 'max:255'],
                'address' => ['nullable', 'string', 'min:5', 'max:500'],
                'website' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/'],
                'remarks' => ['nullable', 'in:Callback,Pre-sale,Sample rejected,Sale'],
                'notes' => ['nullable', 'string', 'max:255'],
            ]);

            // Check if user is admin
            if (!$this->isAdminUser($user)) {
                throw new \Exception('Access denied. Admin privileges required.');
            }

            // Find the callback
            $callback = Callback::findOrFail($request->callback_id);

            // Store old values for logging
            $oldValues = $callback->only([
                'customer_name', 'phone_number', 'email', 'address', 'website', 'remarks', 'notes'
            ]);

            // Update the callback
            $callback->update([
                'customer_name' => $request->customer_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'address' => $request->address,
                'website' => $request->website,
                'remarks' => $request->remarks,
                'notes' => $request->notes,
            ]);

            // Log success
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'updated_callback',
                'details' => json_encode([
                    'username' => $user->username,
                    'callback_id' => $request->callback_id,
                    'old_values' => $oldValues,
                    'new_values' => $request->only([
                        'customer_name', 'phone_number', 'email', 'address', 'website', 'remarks', 'notes'
                    ]),
                ]),
            ]);
            Log::info("Callback {$request->callback_id} updated by {$user->username}");

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Callback updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update_callback_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'callback_id' =>trend,
                    'errors' => $e->errors(),
                ]),
            ]);
            Log::error("Validation error updating callback: " . json_encode($e->errors()));
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update_callback_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'callback_id' => $request->callback_id,
                    'error' => $e->getMessage(),
                ]),
            ]);
            Log::error("Error updating callback: {$e->getMessage()}");
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the callback.',
            ], 500);
        }
    }

    public function getProfile()
    {
        $user = Auth::user();
        try {
            if (!$this->isAdminUser($user)) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'view_profile_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'error' => 'Access denied. Admin privileges required.',
                    ]),
                ]);
                Log::error("User {$user->username} attempted to view profile without admin privileges");
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admin privileges required.'], 403);
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'viewed_profile',
                'details' => json_encode([
                    'username' => $user->username,
                ]),
            ]);
            Log::info("Profile viewed by {$user->username}");

            return response()->json([
                'status' => 'success',
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ]);
        } catch (\Exception $e) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'view_profile_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]),
            ]);
            \Log::error('Error fetching profile: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred while fetching the profile.'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            if (!$this->isAdminUser($user)) {
                throw new \Exception('Access denied. Admin privileges required.');
            }

            $request->validate([
                'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);

            // Store old values for logging
            $oldValues = $user->only(['username', 'email', 'first_name', 'last_name']);
            $passwordChanged = $request->filled('password') ? true : false;

            $user->username = $request->username;
            $user->email = $request->email;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            if ($request->filled('password')) {
                $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            }
            $user->save();

            // Log success
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'updated_profile',
                'details' => json_encode([
                    'username' => $user->username,
                    'old_values' => $oldValues,
                    'new_values' => $request->only(['username', 'email', 'first_name', 'last_name']),
                    'password_changed' => $passwordChanged,
                ]),
            ]);
            \Log::info("Profile updated by {$user->username}");

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Profile updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update_profile_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'errors' => $e->errors(),
                ]),
            ]);
            \Log::error("Validation error updating profile: " . json_encode($e->errors()));
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update_profile_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]),
            ]);
            \Log::error("Error updating profile: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => 'An error occurred while updating the profile.'], 500);
        }
    }
}