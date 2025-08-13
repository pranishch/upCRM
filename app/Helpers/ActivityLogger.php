<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $details = [])
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}