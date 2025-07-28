<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class CallbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function isAdminUser($user)
    {
        return $user && (
            ($user->userProfile && $user->userProfile->role === 'admin') || 
            $user->is_superuser
        );
    }

    protected function getUserRole($user)
    {
        if ($this->isAdminUser($user)) {
            return 'admin';
        }
        return $user->userProfile ? $user->userProfile->role : 'agent';
    }

    protected function canAccessUserCallbacks($user, $targetUser)
    {
        if ($this->isAdminUser($user)) {
            return true;
        }
        if ($user->id === $targetUser->id) {
            return true;
        }
        if ($this->getUserRole($user) === 'manager' && $targetUser->userProfile && $targetUser->userProfile->manager_id === $user->id) {
            return true;
        }
        return false;
    }

    protected function canAddCallbacks($user, $targetUser)
    {
        return $user->id === $targetUser->id && $this->getUserRole($user) !== 'admin';
    }

    public function index(Request $request, $user_id = null)
    {
        $user = Auth::user();
        $user_role = $this->getUserRole($user);
        $can_manage = $this->isAdminUser($user);
        $can_edit_all = $this->isAdminUser($user);
        $can_edit = $can_edit_all || $user_role === 'agent';
        $can_delete = $can_edit_all;
        $search_query = $request->query('q', '');
        $search_field = $request->query('search_field', 'customer_name');

        if ($user_id) {
            $target_user = User::findOrFail($user_id);
            if (!$this->canAccessUserCallbacks($user, $target_user)) {
                return redirect()->route('callbacklist')->with('error', 'Access denied. You can only view your own callbacks or those of authorized users.');
            }
            $callbacks = Callback::where('created_by', $target_user->id);
            $is_viewing_other = true;
        } else {
            $target_user = $user;
            if ($user_role === 'agent') {
                $callbacks = Callback::where('created_by', $user->id);
            } elseif ($user_role === 'manager') {
                $callbacks = Callback::where('manager_id', $user->id);
            } else {
                $callbacks = Callback::query();
            }
            $is_viewing_other = false;
        }

        $can_add = $this->canAddCallbacks($user, $target_user);

        if ($search_query) {
            try {
                if ($search_field === 'all') {
                    $callbacks = $callbacks->where(function (Builder $query) use ($search_query) {
                        $query->where('customer_name', 'like', "%{$search_query}%")
                            ->orWhere('phone_number', 'like', "%{$search_query}%")
                            ->orWhere('email', 'like', "%{$search_query}%")
                            ->orWhere('address', 'like', "%{$search_query}%")
                            ->orWhere('website', 'like', "%{$search_query}%")
                            ->orWhere('remarks', 'like', "%{$search_query}%")
                            ->orWhere('notes', 'like', "%{$search_query}%")
                            ->orWhereHas('createdBy', function (Builder $q) use ($search_query) {
                                $q->where('username', 'like', "%{$search_query}%");
                            });
                    });
                } elseif ($search_field === 'customer_name') {
                    $callbacks = $callbacks->where('customer_name', 'like', "%{$search_query}%");
                } elseif ($search_field === 'phone_number') {
                    $callbacks = $callbacks->where('phone_number', 'like', "%{$search_query}%");
                } elseif ($search_field === 'email') {
                    $callbacks = $callbacks->where('email', 'like', "%{$search_query}%");
                }
            } catch (\Exception $e) {
                Log::error("Search error: {$e->getMessage()}");
                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => 'Invalid search query'], 400);
                }
                return redirect()->route('callbacklist')->with('error', 'An error occurred while processing the search query.');
            }
        }

        $callbacks = $callbacks->orderBy('added_at', 'desc');
        $callbacks = $callbacks->paginate(20);

        $context = compact(
            'user_role',
            'can_manage',
            'can_edit_all',
            'can_edit',
            'can_add',
            'can_delete',
            'search_query',
            'search_field',
            'target_user',
            'is_viewing_other',
            'callbacks'
        );

        if ($request->ajax()) {
            try {
                $callbacks_html = view('callbacklist_table_body', $context)->render();
                $pagination_html = view('callbacklist_pagination', $context)->render();
                return response()->json([
                    'status' => 'success',
                    'callbacks_html' => $callbacks_html,
                    'pagination_html' => $pagination_html,
                    'total_callbacks' => $callbacks->total()
                ]);
            } catch (\Exception $e) {
                Log::error("AJAX rendering error: {$e->getMessage()}");
                return response()->json(['status' => 'error', 'message' => 'Error rendering table content'], 500);
            }
        }

        return view('callbacklist', $context);
    }

    public function save(Request $request)
    {
        if (!$request->isMethod('post')) {
            Log::error("Invalid request method");
            return response()->json(['status' => 'error', 'message' => 'Invalid request method'], 400);
        }

        try {
            $user = Auth::user();
            $user_role = $this->getUserRole($user);
            $can_edit_all = $this->isAdminUser($user);

            Log::debug("User: {$user->username}, Role: {$user_role}, Can edit all: {$can_edit_all}, POST data: " . json_encode($request->all()));

            $content_type = strtolower($request->header('Content-Type', ''));
            if (str_contains($content_type, 'application/json')) {
                $data = $request->json()->all();
                $data = is_array($data) && !isset($data[0]) ? [$data] : $data;
            } else {
                $data = [[
                    'callback_id' => $request->input('callback_id'),
                    'target_user_id' => $request->input('target_user_id'),
                    'customer_name' => trim($request->input('customer_name', '')),
                    'phone_number' => trim($request->input('phone_number', '')),
                    'email' => trim($request->input('email', '')),
                    'address' => trim($request->input('address', '')),
                    'website' => trim($request->input('website', '')),
                    'remarks' => trim($request->input('remarks', '')),
                    'notes' => trim($request->input('notes', '')),
                    'added_at' => $request->input('added_at')
                ]];
            }

            $saved_count = 0;
            $saved_callback_ids = [];

            DB::beginTransaction();

            foreach ($data as $callback_data) {
                $callback_id = $callback_data['callback_id'] ?? null;
                $target_user_id = $callback_data['target_user_id'] ?? null;

                // Determine callback owner
                if ($target_user_id && $can_edit_all) {
                    $callback_owner = User::find($target_user_id);
                    if (!$callback_owner) {
                        Log::error("Target user ID {$target_user_id} does not exist");
                        throw new \Exception("Target user ID {$target_user_id} does not exist");
                    }
                    if (!$this->canAccessUserCallbacks($user, $callback_owner)) {
                        Log::error("User {$user->username} attempted to edit callbacks for unauthorized user {$callback_owner->username}");
                        throw new \Illuminate\Auth\Access\AuthorizationException("You are not authorized to edit callbacks for this user");
                    }
                } else {
                    $callback_owner = $user;
                }

                // Check permission to add new callback
                if (!$callback_id && !$this->canAddCallbacks($user, $callback_owner)) {
                    Log::error("User {$user->username} attempted to add callback for {$callback_owner->username} without permission");
                    throw new \Illuminate\Auth\Access\AuthorizationException("You do not have permission to add callbacks");
                }

                // Validation rules
                $validator = Validator::make($callback_data, [
                    'customer_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
                    'phone_number' => ['required', 'string', 'min:5', 'max:20', 'regex:/^[\+\-0-9\s\(\),./#]+$/'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'address' => ['nullable', 'string', 'min:5', 'max:500'],
                    'website' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'notes' => ['nullable', 'string', 'max:255'],
                    'added_at' => ['nullable', 'date']
                ]);

                if ($validator->fails()) {
                    Log::error("Validation error: " . json_encode($validator->errors()->all()));
                    return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
                }

                $added_at = $callback_data['added_at'] ? Carbon::parse($callback_data['added_at']) : now();

                if ($callback_id) {
                    $callback = Callback::findOrFail($callback_id);
                    if ($can_edit_all) {
                        // Admin: can edit all fields
                        $callback->update([
                            'customer_name' => $callback_data['customer_name'],
                            'phone_number' => $callback_data['phone_number'],
                            'email' => $callback_data['email'] ?: null,
                            'address' => $callback_data['address'] ?: null,
                            'website' => $callback_data['website'] ?: null,
                            'remarks' => $callback_data['remarks'] ?: null,
                            'notes' => $callback_data['notes'] ?: null,
                            'added_at' => $added_at
                        ]);
                    } else {
                        if ($user_role === 'manager') {
                            if ($callback->created_by != $user->id && $callback->manager_id == $user->id) {
                                // Manager editing assigned callback: only remarks and notes
                                $callback->update([
                                    'remarks' => $callback_data['remarks'] ?: null,
                                    'notes' => $callback_data['notes'] ?: null
                                ]);
                            } elseif ($callback->created_by == $user->id) {
                                // Manager editing own callback: all fields
                                $callback->update([
                                    'customer_name' => $callback_data['customer_name'],
                                    'phone_number' => $callback_data['phone_number'],
                                    'email' => $callback_data['email'] ?: null,
                                    'address' => $callback_data['address'] ?: null,
                                    'website' => $callback_data['website'] ?: null,
                                    'remarks' => $callback_data['remarks'] ?: null,
                                    'notes' => $callback_data['notes'] ?: null,
                                    'added_at' => $added_at
                                ]);
                            } else {
                                Log::error("Manager {$user->username} attempted to edit unassigned callback {$callback_id}");
                                throw new \Illuminate\Auth\Access\AuthorizationException("You can only edit callbacks assigned to you or created by you");
                            }
                        } elseif ($user_role === 'agent') {
                            if ($callback->created_by != $user->id) {
                                Log::error("Agent {$user->username} attempted to edit callback {$callback_id} not owned by them");
                                throw new \Illuminate\Auth\Access\AuthorizationException("You can only edit your own callbacks");
                            }
                            $callback->update([
                                'customer_name' => $callback_data['customer_name'],
                                'phone_number' => $callback_data['phone_number'],
                                'email' => $callback_data['email'] ?: null,
                                'address' => $callback_data['address'] ?: null,
                                'website' => $callback_data['website'] ?: null,
                                'remarks' => $callback_data['remarks'] ?: null,
                                'notes' => $callback_data['notes'] ?: null,
                                'added_at' => $added_at
                            ]);
                        }
                    }
                    Log::info("Callback {$callback_id} updated by {$user->username}");
                    $saved_count++;
                    $saved_callback_ids[] = $callback->id;
                } else {
                    // Creating new callback
                    $callback = Callback::create([
                        'created_by' => $callback_owner->id,
                        'manager_id' => ($user_role === 'manager' || $can_edit_all) ? $callback_owner->id : null,
                        'added_at' => $added_at,
                        'customer_name' => $callback_data['customer_name'],
                        'phone_number' => $callback_data['phone_number'],
                        'email' => $callback_data['email'] ?: null,
                        'address' => $callback_data['address'] ?: null,
                        'website' => $callback_data['website'] ?: null,
                        'remarks' => $callback_data['remarks'] ?: null,
                        'notes' => $callback_data['notes'] ?: null
                    ]);
                    Log::info("New callback {$callback->id} created by {$user->username} for user {$callback_owner->username}");
                    if (!Callback::where('id', $callback->id)->exists()) {
                        Log::error("Callback {$callback->id} was not saved in the database");
                        throw new QueryException("Callback {$callback->id} failed to save");
                    }
                    $saved_count++;
                    $saved_callback_ids[] = $callback->id;
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Successfully saved {$saved_count} callback(s).",
                'saved_count' => $saved_count,
                'callback_ids' => $saved_callback_ids,
                'target_user_id' => $target_user_id && $can_edit_all ? $target_user_id : $user->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation error: " . json_encode($e->errors()));
            return response()->json(['status' => 'error', 'message' => $e->errors()[array_key_first($e->errors())][0]], 400);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error("Permission denied: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Database error: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => "Database error: {$e->getMessage()}"], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Unexpected error: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => "Error: {$e->getMessage()}"], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$this->isAdminUser($user)) {
                Log::error("User {$user->username} attempted to delete callback without admin privileges");
                return response()->json(['status' => 'error', 'message' => 'Access denied. Admin privileges required.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'callback_ids' => ['required', 'array'],
                'callback_ids.*' => ['exists:callbacks,id']
            ]);

            if ($validator->fails()) {
                Log::error("Validation error: " . json_encode($validator->errors()->all()));
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
            }

            $callback_ids = $request->input('callback_ids');
            $deleted_count = Callback::whereIn('id', $callback_ids)->delete();

            Log::info("Deleted {$deleted_count} callback(s) by {$user->username}");
            return response()->json([
                'status' => 'success',
                'message' => "Successfully deleted {$deleted_count} callback(s).",
                'deleted_count' => $deleted_count
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting callback: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => "Error: {$e->getMessage()}"], 500);
        }
    }
}