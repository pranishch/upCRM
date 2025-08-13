<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagerDashboardController extends Controller
{
    public function show(Request $request, $manager_id)
    {
        $user = Auth::user();
        $manager = User::findOrFail($manager_id);

        // Check if user can access the manager's dashboard
        if (!$this->canAccessManagerDashboard($user, $manager)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'view_manager_dashboard_failed',
                'details' => json_encode([
                    'username' => $user->username,
                    'manager_id' => $manager_id,
                    'error' => 'Access denied to manager dashboard',
                ]),
            ]);
            Log::error("User {$user->username} attempted to access unauthorized manager dashboard {$manager_id}");
            return redirect()->route('callbacks.index')->with('error', 'Access denied. You can only view your own dashboard.');
        }

        if ($request->isMethod('post')) {
            if (!(Auth::user()->role === 'admin' || Auth::user()->is_superuser)) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'manager_dashboard_action_failed',
                    'details' => json_encode([
                        'username' => $user->username,
                        'manager_id' => $manager_id,
                        'action' => $request->input('action'),
                        'error' => 'Access denied. Admin privileges required.',
                    ]),
                ]);
                Log::error("User {$user->username} attempted manager dashboard action without admin privileges");
                return redirect()->route('manager_dashboard', $manager_id)->with('error', 'Access denied. Admin privileges required.');
            }

            $action = $request->input('action');
            if ($action == 'assign_agent') {
                $agent_id = $request->input('agent_id');
                $agent = User::findOrFail($agent_id);
                if ($agent->userProfile && $agent->userProfile->role != 'agent') {
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => 'assign_agent_failed',
                        'details' => json_encode([
                            'username' => $user->username,
                            'manager_id' => $manager_id,
                            'agent_id' => $agent_id,
                            'error' => 'Only agents can be assigned',
                        ]),
                    ]);
                    Log::error("Attempted to assign non-agent {$agent_id} to manager {$manager_id}");
                    return redirect()->route('manager_dashboard', $manager_id)->with('error', 'Only agents can be assigned to managers.');
                }
                $profile = $agent->userProfile ?? UserProfile::create(['user_id' => $agent->id]);
                $profile->manager_id = $manager->id;
                $profile->save();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'assigned_agent',
                    'details' => json_encode([
                        'username' => $user->username,
                        'manager_id' => $manager_id,
                        'agent_id' => $agent_id,
                        'agent_username' => $agent->username,
                    ]),
                ]);
                Log::info("Agent {$agent->username} assigned to manager {$manager->username} by {$user->username}");
                return redirect()->route('manager_dashboard', $manager_id)->with('success', "Agent {$agent->username} assigned to {$manager->username}.");
            } elseif ($action == 'unassign_agent') {
                $agent_id = $request->input('agent_id');
                $agent = User::findOrFail($agent_id);
                $profile = $agent->userProfile ?? UserProfile::create(['user_id' => $agent->id]);
                $profile->manager_id = null;
                $profile->save();
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'unassigned_agent',
                    'details' => json_encode([
                        'username' => $user->username,
                        'manager_id' => $manager_id,
                        'agent_id' => $agent_id,
                        'agent_username' => $agent->username,
                    ]),
                ]);
                Log::info("Agent {$agent->username} unassigned from manager {$manager->username} by {$user->username}");
                return redirect()->route('manager_dashboard', $manager_id)->with('success', "Agent {$agent->username} unassigned from {$manager->username}.");
            }
        }

        $agents = User::whereHas('userProfile', function ($query) use ($manager) {
            $query->where('manager_id', $manager->id)->where('role', 'agent');
        })->with(['userProfile'])->get();

        $available_agents = User::where(function ($query) {
            $query->whereHas('userProfile', function ($q) {
                $q->where('role', 'agent');
            })->orWhereDoesntHave('userProfile');
        })->whereDoesntHave('userProfile', function ($query) use ($manager) {
            $query->where('manager_id', $manager->id);
        })->with(['userProfile'])->get();

        $search_query = $request->input('q', '');
        $search_field = $request->input('search_field', 'all');

        $callbacks = Callback::where('manager_id', $manager->id)
            ->orderBy('added_at', 'desc')
            ->with(['createdBy.userProfile']); 

        if ($search_query) {    
            $callbacks = $callbacks->when($search_field == 'all', function ($query) use ($search_query) {
                return $query->where(function ($q) use ($search_query) {
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
            })->when($search_field == 'customer_name', function ($query) use ($search_query) {
                return $query->where('customer_name', 'like', "%{$search_query}%");
            })->when($search_field == 'phone_number', function ($query) use ($search_query) {
                return $query->where('phone_number', 'like', "%{$search_query}%");
            })->when($search_field == 'email', function ($query) use ($search_query) {
                return $query->where('email', 'like', "%{$search_query}%");
            });
        }

        $page_obj = $callbacks->paginate(20);

        $context = [
            'manager' => $manager,
            'manager_username' => $manager->username,
            'agents' => $agents,
            'available_agents' => $available_agents,
            'user_role' => Auth::user()->userProfile->role ?? 'agent',
            'can_edit' => Auth::user()->is_superuser || in_array(Auth::user()->userProfile->role ?? 'agent', ['admin', 'manager']),
            'callbacks' => $page_obj->items(),
            'page_obj' => $page_obj,
            'search_query' => $search_query,
            'search_field' => $search_field,
        ];

        if ($request->ajax()) {
            return response()->json([
                'callbacks_html' => view('manager_dashboard_callbacks', $context)->render(),
                'pagination_html' => view('manager_dashboard_pagination', $context)->render(),
            ]);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'viewed_manager_dashboard',
            'details' => json_encode([
                'username' => $user->username,
                'manager_id' => $manager_id,
                'total_agents' => $agents->count(),
                'total_callbacks' => $page_obj->total(),
                'search_query' => $search_query,
                'search_field' => $search_field,
            ]),
        ]);
        Log::info("Manager dashboard viewed by {$user->username} for manager {$manager_id}");

        return view('manager_dashboard', $context);
    }

    private function canAccessManagerDashboard($user, $manager)
    {
        return $user->id === $manager->id || $user->role === 'admin' || $user->is_superuser;
    }
}