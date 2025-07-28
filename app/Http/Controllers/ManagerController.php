<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Callback;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $managers = User::whereHas('userprofile', function ($query) {
            $query->where('role', 'manager');
        })->with(['userprofile', 'groups'])->get();

        $roles = ['agent', 'manager'];
        $callbacks = Callback::whereIn('created_by_id', $managers->pluck('id'))
            ->orderBy('added_at', 'desc')
            ->get();

        return view('manage_managers', compact('managers', 'roles', 'callbacks'));
    }

    public function store(Request $request)
    {
        if ($request->input('action') === 'create') {
            $validator = Validator::make($request->all(), [
                'username' => 'required|unique:users,username',
                'email' => 'nullable|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
                'role' => 'required|in:manager',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
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

            return redirect()->route('managers.index')
                ->with('success', "Manager {$user->username} created successfully!");
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function update(Request $request)
    {
        if ($request->input('action') === 'edit') {
            $user = User::findOrFail($request->user_id);

            if ($user->id === Auth::id() && !$this->isAdminUser(Auth::user())) {
                return redirect()->route('managers.index')
                    ->with('error', 'You cannot edit your own details.');
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|unique:users,username,' . $user->id,
                'email' => 'nullable|email|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->username = $request->username;
            $user->email = $request->email ?: null;
            $user->save();

            return redirect()->route('managers.index')
                ->with('success', "Manager {$user->username} updated successfully!");
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function changeRole(Request $request)
    {
        if ($request->input('action') === 'change_role') {
            $user = User::findOrFail($request->user_id);

            if ($user->id === Auth::id() && !$this->isAdminUser(Auth::user())) {
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

            $new_role = $request->new_role;
            $user->groups()->detach();
            $group = Group::where('name', ucfirst($new_role))->first();
            if ($group) {
                $user->groups()->attach($group->id);
            }

            $profile = $user->userprofile ?: UserProfile::create(['user_id' => $user->id]);
            $profile->role = $new_role;
            $profile->save();

            return redirect()->route('managers.index')
                ->with('success', "Manager {$user->username} role changed to {$new_role}.");
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function resetPassword(Request $request)
    {
        if ($request->input('action') === 'reset_password') {
            $user = User::findOrFail($request->user_id);

            if ($user->id === Auth::id() && !$this->isAdminUser(Auth::user())) {
                return redirect()->route('managers.index')
                    ->with('error', 'You cannot reset your own password.');
            }

            $validator = Validator::make($request->all(), [
                'new_password' => 'required|min:8',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->route('managers.index')
                ->with('success', "Password reset for {$user->username}!");
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $username = $user->username;
        $user->delete();

        return redirect()->route('managers.index')
            ->with('success', "Manager {$username} deleted successfully!");
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
                'customer_name' => 'required|regex:/^[A-Za-z\s]+$/|min:2',
                'phone_number' => 'required|regex:/^[\+\-0-9\s\(\),./#]+$/|min:5',
                'email' => 'nullable|email',
                'address' => 'nullable|min:5',
                'website' => 'nullable|url|max:255',
                'notes' => 'nullable|max:255',
                'added_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->route('managers.index')
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

            return redirect()->route('managers.index')
                ->with('success', "Callback {$callback->id} updated successfully!");
        }

        return redirect()->route('managers.index')->with('error', 'Invalid action.');
    }

    protected function isAdminUser($user)
    {
        return $user->userprofile && $user->userprofile->role === 'admin';
    }
}