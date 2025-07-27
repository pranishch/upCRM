<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Placeholder routes for redirection (implement later)
Route::get('/admin/dashboard', function () {
    return 'Admin Dashboard';
})->name('admin.dashboard')->middleware(['auth', 'admin']);

Route::get('/manager/dashboard/{manager_id}', function ($manager_id) {
    return "Manager Dashboard for ID: $manager_id";
})->name('manager.dashboard')->middleware(['auth', 'access.manager.dashboard']);

Route::get('/callbacks', function () {
    return 'Callback List';
})->name('callbacks.index')->middleware('auth');