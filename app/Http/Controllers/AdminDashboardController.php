<?php

     namespace App\Http\Controllers;

     use App\Models\User;
     use App\Models\UserProfile;
     use App\Models\Callback;
     use Illuminate\Http\Request;
     use Illuminate\Support\Facades\Auth;
     use Illuminate\Support\Facades\Log;
     use Illuminate\Support\Facades\Validator;
     use Illuminate\Support\Carbon;
     use Illuminate\Support\Facades\DB;
     use Illuminate\Http\JsonResponse;

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
             return $user && ($user->userprofile && $user->userprofile->role === 'admin' || $user->is_superuser);
         }

         public function index(Request $request)
         {
             // Ensure all users have a UserProfile
             User::whereDoesntHave('userProfile')->get()->each(function ($user) {
                 UserProfile::create(['user_id' => $user->id, 'role' => 'agent']);
             });

             // Fetch users excluding admins
             $users = User::whereHas('userProfile', function ($query) {
                 $query->whereIn('role', ['manager', 'agent']);
             })->with(['userProfile', 'groups'])->get();

             $total_users = $users->count();
             $total_managers = User::whereHas('userProfile', function ($query) {
                 $query->where('role', 'manager');
             })->count();
             $managers = User::whereHas('userProfile', function ($query) {
                 $query->where('role', 'manager');
             })->with(['userProfile', 'groups'])->get();

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

         public function updateCallback(Request $request)
         {
             if (!$this->isAdminUser(Auth::user())) {
                 return redirect()->route('admin_dashboard')->with('error', 'Access denied. Admin privileges required.');
             }

             $validator = Validator::make($request->all(), [
                 'callback_id' => 'required|exists:callbacks,id',
                 'customer_name' => 'required|string|min:2|regex:/^[A-Za-z\s]+$/',
                 'phone_number' => 'required|string|min:5|regex:/^[\+\-0-9\s\(\),./#]+$/',
                 'email' => 'nullable|email|max:100',
                 'address' => 'nullable|string|min:5',
                 'website' => 'nullable|url|regex:/^https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/|max:255',
                 'remarks' => 'nullable|string|max:255',
                 'notes' => 'nullable|string|max:255',
                 'added_at' => 'nullable|date_format:Y-m-d H:i:s',
             ]);

             if ($validator->fails()) {
                 return redirect()->route('admin_dashboard')->withErrors($validator)->withInput();
             }

             try {
                 $callback = Callback::findOrFail($request->callback_id);
                 $added_at = $request->added_at ? Carbon::parse($request->added_at) : now();

                 $callback->update([
                     'customer_name' => $request->customer_name,
                     'phone_number' => $request->phone_number,
                     'email' => $request->email ?: null,
                     'address' => $request->address ?: null,
                     'website' => $request->website ?: null,
                     'remarks' => $request->remarks ?: null,
                     'notes' => $request->notes ?: null,
                     'added_at' => $added_at,
                 ]);

                 return redirect()->route('admin_dashboard')->with('success', "Callback {$callback->id} updated successfully!");
             } catch (\Exception $e) {
                 Log::error("Error updating callback: {$e->getMessage()}");
                 return redirect()->route('admin_dashboard')->with('error', 'An error occurred while updating the callback.');
             }
         }

         public function saveCallbacks(Request $request)
         {
             if ($request->header('Content-Type') !== 'application/json') {
                 return response()->json(['status' => 'error', 'message' => 'Invalid Content-Type'], 400);
             }

             $data = $request->json()->all();
             if (!is_array($data)) {
                 $data = [$data];
             }

             $user = Auth::user();
             $user_role = $this->getUserRole($user);
             $can_edit_all = $this->isAdminUser($user);
             $saved_count = 0;
             $saved_callback_ids = [];

             try {
                 DB::beginTransaction();

                 foreach ($data as $callback_data) {
                     $validator = Validator::make($callback_data, [
                         'callback_id' => 'nullable|exists:callbacks,id',
                         'target_user_id' => 'nullable|exists:users,id',
                         'customer_name' => 'required|string|min:2|regex:/^[A-Za-z\s]+$/',
                         'phone_number' => 'required|string|min:5|regex:/^[\+\-0-9\s\(\),./#]+$/',
                         'email' => 'nullable|email|max:100',
                         'address' => 'nullable|string|min:5',
                         'website' => 'nullable|url|regex:/^https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/|max:255',
                         'remarks' => 'nullable|string|max:255',
                         'notes' => 'nullable|string|max:255',
                         'added_at' => 'nullable|date_format:Y-m-d H:i:s',
                     ]);

                     if ($validator->fails()) {
                         return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
                     }

                     $callback_owner = $can_edit_all && $callback_data['target_user_id']
                         ? User::findOrFail($callback_data['target_user_id'])
                         : $user;

                     if (!$callback_data['callback_id'] && !$this->canAddCallbacks($user, $callback_owner)) {
                         return response()->json(['status' => 'error', 'message' => 'You do not have permission to add callbacks'], 403);
                     }

                     $added_at = $callback_data['added_at'] ? Carbon::parse($callback_data['added_at']) : now();

                     if ($callback_data['callback_id']) {
                         $callback = Callback::findOrFail($callback_data['callback_id']);
                         if ($can_edit_all) {
                             $callback->update([
                                 'customer_name' => $callback_data['customer_name'],
                                 'phone_number' => $callback_data['phone_number'],
                                 'email' => $callback_data['email'] ?: null,
                                 'address' => $callback_data['address'] ?: null,
                                 'website' => $callback_data['website'] ?: null,
                                 'remarks' => $callback_data['remarks'] ?: null,
                                 'notes' => $callback_data['notes'] ?: null,
                                 'added_at' => $added_at,
                             ]);
                         } else {
                             if ($user_role == 'manager' && ($callback->created_by_id == $user->id || $callback->manager_id == $user->id)) {
                                 $callback->update([
                                     'remarks' => $callback_data['remarks'] ?: null,
                                     'notes' => $callback_data['notes'] ?: null,
                                 ]);
                             } elseif ($user_role == 'agent' && $callback->created_by_id == $user->id) {
                                 $callback->update([
                                     'customer_name' => $callback_data['customer_name'],
                                     'phone_number' => $callback_data['phone_number'],
                                     'email' => $callback_data['email'] ?: null,
                                     'address' => $callback_data['address'] ?: null,
                                     'website' => $callback_data['website'] ?: null,
                                     'remarks' => $callback_data['remarks'] ?: null,
                                     'notes' => $callback_data['notes'] ?: null,
                                     'added_at' => $added_at,
                                 ]);
                             } else {
                                 return response()->json(['status' => 'error', 'message' => 'You can only edit your own or assigned callbacks'], 403);
                             }
                         }
                         Log::info("Callback {$callback->id} updated by {$user->username}");
                         $saved_count++;
                         $saved_callback_ids[] = $callback->id;
                     } else {
                         $callback = Callback::create([
                             'created_by_id' => $callback_owner->id,
                             'manager_id' => ($user_role == 'manager' || $can_edit_all) ? $callback_owner->id : null,
                             'customer_name' => $callback_data['customer_name'],
                             'phone_number' => $callback_data['phone_number'],
                             'email' => $callback_data['email'] ?: null,
                             'address' => $callback_data['address'] ?: null,
                             'website' => $callback_data['website'] ?: null,
                             'remarks' => $callback_data['remarks'] ?: null,
                             'notes' => $callback_data['notes'] ?: null,
                             'added_at' => $added_at,
                         ]);
                         Log::info("New callback {$callback->id} created by {$user->username} for user {$callback_owner->username}");
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
                     'target_user_id' => $can_edit_all ? ($callback_data['target_user_id'] ?? $user->id) : $user->id,
                 ]);
             } catch (\Exception $e) {
                 DB::rollBack();
                 Log::error("Error saving callbacks: {$e->getMessage()}");
                 return response()->json(['status' => 'error', 'message' => "Error: {$e->getMessage()}"], 500);
             }
         }

         public function deleteCallback(Request $request)
         {
             if (!$this->isAdminUser(Auth::user())) {
                 return response()->json(['status' => 'error', 'message' => 'Access denied. Admin privileges required.'], 403);
             }

             $validator = Validator::make($request->json()->all(), [
                 'callback_ids' => 'required|array',
                 'callback_ids.*' => 'exists:callbacks,id',
             ]);

             if ($validator->fails()) {
                 return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
             }

             try {
                 $deleted_count = Callback::whereIn('id', $request->json('callback_ids'))->delete();
                 if ($deleted_count == 0) {
                     return response()->json(['status' => 'error', 'message' => 'No valid callbacks found for deletion.'], 404);
                 }
                 return response()->json([
                     'status' => 'success',
                     'message' => "Successfully deleted {$deleted_count} callback(s).",
                 ]);
             } catch (\Exception $e) {
                 Log::error("Error deleting callback: {$e->getMessage()}");
                 return response()->json(['status' => 'error', 'message' => "Error: {$e->getMessage()}"], 500);
             }
         }

         public function assignManager(Request $request)
         {
             if (!$this->isAdminUser(Auth::user())) {
                 return response()->json(['status' => 'error', 'message' => 'Access denied. Admin privileges required.'], 403);
             }

             $validator = Validator::make($request->json()->all(), [
                 'callback_id' => 'required|exists:callbacks,id',
                 'manager_id' => 'nullable|exists:users,id',
             ]);

             if ($validator->fails()) {
                 return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
             }

             try {
                 $callback = Callback::findOrFail($request->json('callback_id'));
                 if ($callback->createdBy->userProfile && $callback->createdBy->userProfile->role == 'manager') {
                     return response()->json([
                         'status' => 'error',
                         'message' => 'Cannot reassign manager for callbacks created by a manager.',
                     ], 403);
                 }

                 if ($request->json('manager_id')) {
                     $manager = User::findOrFail($request->json('manager_id'));
                     if (!$manager->userProfile || $manager->userProfile->role != 'manager') {
                         return response()->json(['status' => 'error', 'message' => 'Selected user is not a manager.'], 400);
                     }
                     $callback->update(['manager_id' => $manager->id]);
                     return response()->json([
                         'status' => 'success',
                         'message' => "Callback assigned to manager {$manager->username}.",
                     ]);
                 } else {
                     $callback->update(['manager_id' => null]);
                     return response()->json([
                         'status' => 'success',
                         'message' => 'Callback unassigned from manager.',
                     ]);
                 }
             } catch (\Exception $e) {
                 Log::error("Error assigning manager: {$e->getMessage()}");
                 return response()->json(['status' => 'error', 'message' => "Error: {$e->getMessage()}"], 500);
             }
         }

         protected function getUserRole($user)
         {
             return $user->userProfile->role ?? 'agent';
         }

         protected function canAddCallbacks($user, $callback_owner)
         {
             return $user->id == $callback_owner->id || $this->isAdminUser($user);
         }
     }
     ?>