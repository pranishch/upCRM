<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'email', 'first_name', 'last_name', 'password', 'is_active', 'is_superuser'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function callbacksCreated()
    {
        return $this->hasMany(Callback::class, 'created_by');
    }

    public function assignedCallbacks()
    {
        return $this->hasMany(Callback::class, 'manager_id');
    }

    public function managedAgents()
    {
        return $this->hasMany(UserProfile::class, 'manager_id');
    }

    public function getRoleAttribute()
    {
        return $this->userProfile ? $this->userProfile->role : 'agent';
    }
}