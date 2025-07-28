<?php

     namespace App\Http\Controllers;

     use App\Models\User;
     use App\Models\UserProfile;
     use App\Models\Callback;
     use App\Models\Group;
     use Illuminate\Http\Request;
     use Illuminate\Support\Facades\Auth;
     use Illuminate\Support\Facades\Hash;
     use Illuminate\Support\MessageBag;
     use Illuminate\Support\Facades\Validator;
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
             return $user && ($user->userprofile && $user->userprofile->role === 'admin' || $user->is_superuser);
         }

         public function index(Request $request)
         {
             $users = User::whereHas('userprofile', function (Builder $query) {
                 $query->whereIn('role', ['agent', 'manager']);
             })->orWhereDoesntHave('userprofile')
                 ->where('is_superuser', false)
                 ->with(['userprofile', 'groups'])
                 ->get();

             $total_users = User::count();
             $roles = ['agent', 'manager'];
             $callbacks = Callback::whereIn('created_by_id', $users->pluck('id'))
                 ->orderBy('added_at', 'desc')
                 ->get();

             return view('manage_users', compact('users', 'total_users', 'roles', 'callbacks'));
         }

         public function store(Request $request)
         {
             if ($request->input('action') === 'create') {
                 $validator = Validator::make($request->all(), [
                     'username' => 'required|unique:users,username',
                     'email' => 'nullable|email|unique:users,email',
                     'password' => 'required|confirmed|min:8',
                     'role' => 'required|in:agent,manager',
                 ]);

                 if ($validator->fails()) {
                     return redirect()->route('users.index')
                         ->withErrors($validator)
                         ->withInput();
                 }

                 $user = User::create([
                     'username' => $request->username,
                     'email' => $request->email,
                     'password' => Hash::make($request->password),
                 ]);

                 $role = $request->role;
                 UserProfile::create([
                     'user_id' => $user->id,
                     'role' => $role,
                 ]);

                 $group = Group::where('name', ucfirst($role))->first();
                 if ($group) {
                     $user->groups()->attach($group->id);
                 }

                 return redirect()->route('users.index')
                     ->with('success', "User {$user->username} created successfully with role {$role}!");
             }

             return redirect()->route('users.index')->with('error', 'Invalid action.');
         }

         public function update(Request $request)
         {
             if ($request->input('action') === 'edit') {
                 $user = User::findOrFail($request->user_id);

                 if ($user->id === Auth::id() && !$this->isAdminUser(Auth::user())) {
                     return redirect()->route('users.index')
                         ->with('error', 'You cannot edit your own details.');
                 }

                 $validator = Validator::make($request->all(), [
                     'username' => 'required|unique:users,username,' . $user->id,
                     'email' => 'nullable|email|unique:users,email,' . $user->id,
                 ]);

                 if ($validator->fails()) {
                     return redirect()->route('users.index')
                         ->withErrors($validator)
                         ->withInput();
                 }

                 $user->username = $request->username;
                 $user->email = $request->email ?: null;
                 $user->save();

                 return redirect()->route('users.index')
                     ->with('success', "User {$user->username} updated successfully!");
             }

             return redirect()->route('users.index')->with('error', 'Invalid action.');
         }

         public function changeRole(Request $request)
         {
             if ($request->input('action') === 'change_role') {
                 $user = User::findOrFail($request->user_id);

                 if ($user->id === Auth::id() && !$this->isAdminUser(Auth::user())) {
                     return redirect()->route('users.index')
                         ->with('error', 'You cannot change your own role.');
                 }

                 $validator = Validator::make($request->all(), [
                     'new_role' => 'required|in:agent,manager',
                 ]);

                 if ($validator->fails()) {
                     return redirect()->route('users.index')
                         ->withErrors($validator)
                         ->withInput();
                 }

                 $new_role = $request->new_role;
                 $user->groups()->detach();
                 $group = Group::where('name', ucfirst($new_role))->first();
                 if ($group) {
                     $user->groups()->attach($group->id);
                 }

                 $profile = $user->userprofile ?: UserProfile::create(['user_id' => $user->id]);
                 $profile->role = $new_role;
                 $profile->save();

                 return redirect()->route('users.index')
                     ->with('success', "User {$user->username} role changed to {$new_role}.");
             }

             return redirect()->route('users.index')->with('error', 'Invalid action.');
         }

         public function resetPassword(Request $request)
         {
             if ($request->input('action') === 'reset_password') {
                 $user = User::findOrFail($request->user_id);

                 if ($user->id === Auth::id() && !$this->isAdminUser(Auth::user())) {
                     return redirect()->route('users.index')
                         ->with('error', 'You cannot reset your own password.');
                 }

                 $validator = Validator::make($request->all(), [
                     'new_password' => 'required|min:8',
                 ]);

                 if ($validator->fails()) {
                     return redirect()->route('users.index')
                         ->withErrors($validator)
                         ->withInput();
                 }

                 $user->password = Hash::make($request->new_password);
                 $user->save();

                 return redirect()->route('users.index')
                     ->with('success', "Password reset for {$user->username}!");
             }

             return redirect()->route('users.index')->with('error', 'Invalid action.');
         }

         public function destroy($id)
         {
             $user = User::findOrFail($id);
             $username = $user->username;
             $user->delete();

             return redirect()->route('users.index')
                 ->with('success', "User {$username} deleted successfully!");
         }

         public function updateCallback(Request $request)
         {
             if ($request->input('action') === 'edit_callback') {
                 if (!$this->isAdminUser(Auth::user())) {
                     return redirect()->route('users.index')
                         ->with('error', 'Access denied. Admin privileges required to edit callbacks.');
                 }

                 $callback = Callback::findOrFail($request->callback_id);

                 $validator = Validator::make($request->all(), [
                     'customer_name' => 'required|regex:/^[A-Za-z\s]+$/|min:2',
                     'phone_number' => 'required|regex:/^[\+\-0-9\s\(\),./#]+$/|min:5',
                     'email' => 'nullable|email',
                     'address' => 'nullable|min:5',
                     'website' => 'nullable|url|max:255',
                     'notes' => 'nullable|max:255',
                     'added_at' => 'nullable|date',
                 ]);

                 if ($validator->fails()) {
                     return redirect()->route('users.index')
                         ->withErrors($validator)
                         ->withInput();
                 }

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

                 return redirect()->route('users.index')
                     ->with('success', "Callback {$callback->id} updated successfully!");
             }

             return redirect()->route('users.index')->with('error', 'Invalid action.');
         }
     }
     ?>