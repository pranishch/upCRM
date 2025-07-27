<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserManagementRequest extends FormRequest
{
    public function authorize()
    {
        if ($this->user && $this->user->is_superuser && !Auth::user()->is_superuser) {
            return false;
        }
        return Auth::user()->is_superuser || (Auth::user()->userProfile && Auth::user()->userProfile->role === 'admin');
    }

    public function rules()
    {
        return [
            'username' => 'required|string|max:255|unique:users,username,' . ($this->user ? $this->user->id : null),
            'email' => 'required|email|max:255|unique:users,email,' . ($this->user ? $this->user->id : null),
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'role' => 'required|in:agent,manager,admin',
            'department' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'This email address is already in use.',
        ];
    }
}