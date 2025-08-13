<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', function ($request, $next) {
            if (!auth()->user()->is_superuser && auth()->user()->userProfile?->role !== 'admin') {
                return redirect()->route('callbacklist')->with('error', 'Unauthorized access');
            }
            return $next($request);
        }]);
    }

    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'user.userProfile'])->latest();

        // Filter by username
        if ($request->has('username') && $request->username !== '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->username . '%');
            });
        }

        // Filter by role
        if ($request->has('role') && in_array($request->role, ['admin', 'manager', 'user'])) {
            $query->whereHas('user', function ($q) use ($request) {
                if ($request->role === 'admin') {
                    $q->where('is_superuser', true)
                    ->orWhereHas('userProfile', function ($q2) {
                        $q2->where('role', 'admin');
                    });
                } else {
                    $q->whereHas('userProfile', function ($q2) use ($request) {
                        $q2->whereIn('role', [$request->role, $request->role === 'user' ? 'agent' : $request->role]);
                    });
                }
            });
        }

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $logs = $query->paginate($perPage)->appends($request->only(['username', 'role', 'per_page']));
        
        $paginationInfo = [
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'per_page' => $logs->perPage(),
            'total' => $logs->total(),
            'from' => $logs->firstItem(),
            'to' => $logs->lastItem(),
        ];

        if ($request->ajax()) {
            // Generate table rows HTML
            $html = '';
            if ($logs->count() > 0) {
                foreach ($logs as $log) {
                    $username = $log->user ? ($log->user->username ?? 'Unknown') : 'System';
                    
                    // Determine role and badge
                    $role = 'User';
                    $badgeClass = 'bg-info';
                    if ($log->user) {
                        if ($log->user->is_superuser) {
                            $role = 'Admin';
                            $badgeClass = 'bg-danger';
                        } elseif ($log->user->userProfile && $log->user->userProfile->role === 'admin') {
                            $role = 'Admin';
                            $badgeClass = 'bg-danger';
                        } elseif ($log->user->userProfile && $log->user->userProfile->role === 'manager') {
                            $role = 'Manager';
                            $badgeClass = 'bg-warning';
                        }
                    } else {
                        $role = 'System';
                        $badgeClass = 'bg-dark';
                    }
                    
                    $html .= '<tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-2"><i class="fas fa-user"></i></div>
                                <span>' . $username . '</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge ' . $badgeClass . ' rounded-pill">' . $role . '</span>
                        </td>
                        <td>' . $log->action . '</td>
                        <td>
                            <div>' . $log->created_at->format('M d, Y') . '</div>
                            <small class="text-muted">' . $log->created_at->format('H:i:s') . '</small>
                        </td>
                    </tr>';
                }
            } else {
                $html = '<tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No logs found</h5>
                    </td>
                </tr>';
            }
            
            return response()->json([
                'html' => $html,
                'pagination_info' => $paginationInfo
            ]);
        }

        return view('activity_logs.index', compact('logs', 'paginationInfo'));
    }
}