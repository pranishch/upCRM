<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhpInfoController extends Controller
{
    public function show()
    {
        // Capture phpinfo output
        ob_start();
        phpinfo();
        $phpInfo = ob_get_clean();

        return response($phpInfo);
    }
}