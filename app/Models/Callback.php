<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Callback extends Model
{
    protected $fillable = [
        'customer_name', 'address', 'phone_number', 'email', 'website',
        'remarks', 'notes', 'created_by', 'manager_id', 'is_completed', 'added_at'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}