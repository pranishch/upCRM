<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        // Ensure all users have a UserProfile
        User::whereDoesntHave('userProfile')->get()->each(function ($user) {
            UserProfile::create(['user_id' => $user->id, 'role' => 'agent']);
        });

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

        return view('admin_dashboard', [
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
        ]);
    }

    public function assignManager(Request $request)
    {
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
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Selected user is not a manager.',
                    ], 400);
                }
            }

            // Update the callback's manager_id
            $callback->manager_id = $request->manager_id ?: null;
            $callback->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Manager assigned successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error assigning manager: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while assigning the manager.',
            ], 500);
        }
    }
    public function updateCallback(Request $request)
    {
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
            $user = Auth::user();
            if (!$this->isAdminUser($user)) {
                Log::error("User {$user->username} attempted to update callback without admin privileges");
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admin privileges required.'], 403);
            }

            // Find the callback
            $callback = Callback::findOrFail($request->callback_id);

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

            Log::info("Callback {$request->callback_id} updated by {$user->username}");
            return response()->json([
                'status' => 'success',
                'message' => 'Callback updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation error: " . json_encode($e->errors()));
            return response()->json(['status' => 'error', 'message' => $e->errors()[array_key_first($e->errors())][0]], 400);
        } catch (\Exception $e) {
            Log::error("Error updating callback: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => "Error: {$e->getMessage()}"], 500);
        }
    }
}