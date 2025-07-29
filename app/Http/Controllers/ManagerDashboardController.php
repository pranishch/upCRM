<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerDashboardController extends Controller
{
    public function show(Request $request, $manager_id)
    {
        $manager = User::findOrFail($manager_id);

        // Check if user can access the manager's dashboard
        if (!$this->canAccessManagerDashboard(Auth::user(), $manager)) {
            return redirect()->route('callbacks.index')->with('error', 'Access denied. You can only view your own dashboard.');
        }

        if ($request->isMethod('post')) {
            if (!(Auth::user()->role === 'admin' || Auth::user()->is_superuser)) {
                return redirect()->route('manager_dashboard', $manager_id)->with('error', 'Access denied. Admin privileges required.');
            }

            $action = $request->input('action');
            if ($action == 'assign_agent') {
                $agent_id = $request->input('agent_id');
                $agent = User::findOrFail($agent_id);
                if ($agent->userProfile && $agent->userProfile->role != 'agent') {
                    return redirect()->route('manager_dashboard', $manager_id)->with('error', 'Only agents can be assigned to managers.');
                }
                $profile = $agent->userProfile ?? UserProfile::create(['user_id' => $agent->id]);
                $profile->manager_id = $manager->id;
                $profile->save();
                return redirect()->route('manager_dashboard', $manager_id)->with('success', "Agent {$agent->username} assigned to {$manager->username}.");
            } elseif ($action == 'unassign_agent') {
                $agent_id = $request->input('agent_id');
                $agent = User::findOrFail($agent_id);
                $profile = $agent->userProfile ?? UserProfile::create(['user_id' => $agent->id]);
                $profile->manager_id = null;
                $profile->save();
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
            ->with(['created_by.userProfile']);

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
                      ->orWhereHas('created_by', function ($q) use ($search_query) {
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

        return view('manager_dashboard', $context);
    }

    private function canAccessManagerDashboard($user, $manager)
    {
        return $user->id === $manager->id || $user->role === 'admin' || $user->is_superuser;
    }
}
