<?php

  use Illuminate\Support\Facades\Route;
  use App\Http\Controllers\AuthController;
  use App\Http\Controllers\UserController;
  use App\Http\Controllers\ManagerController;
  use App\Http\Controllers\CallbackController;
  use App\Http\Controllers\AdminDashboardController;
  use App\Http\Controllers\ManagerDashboardController;
  use App\Http\Controllers\PhpInfoController;
  
  Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
  Route::get('/phpinfo', [PhpInfoController::class, 'show'])->name('phpinfo')->middleware('auth'); 
  Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.post');
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
  Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

  Route::middleware(['auth'])->group(function () {
      Route::get('/users', [UserController::class, 'index'])->name('users.index');
      Route::get('/manage-users', [UserController::class, 'index'])->name('manage_users');
      Route::post('/users', [UserController::class, 'store'])->name('users.store');
      Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
      Route::post('/users/change-role', [UserController::class, 'changeRole'])->name('users.change_role');
      Route::post('/users/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');
      Route::get('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');
      Route::post('/users/callback/update', [UserController::class, 'updateCallback'])->name('users.update_callback');

      Route::get('/managers', [ManagerController::class, 'index'])->name('managers.index');
      Route::get('/manage-managers', [ManagerController::class, 'index'])->name('manage_managers');
      Route::post('/managers', [ManagerController::class, 'store'])->name('managers.store');
      Route::post('/managers/update', [ManagerController::class, 'update'])->name('managers.update');
      Route::post('/managers/change-role', [ManagerController::class, 'changeRole'])->name('managers.change_role');
      Route::post('/managers/reset-password', [ManagerController::class, 'resetPassword'])->name('managers.reset_password');
      Route::get('/managers/{id}/delete', [ManagerController::class, 'destroy'])->name('managers.delete');
      Route::post('/managers/callback/update', [ManagerController::class, 'updateCallback'])->name('managers.update_callback');

      Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin_dashboard');
      Route::post('/admin/dashboard', [AdminDashboardController::class, 'updateCallback'])->name('admin_dashboard.update');
      Route::post('/assign_manager', [AdminDashboardController::class, 'assignManager'])->name('assign_manager');

      Route::get('/manager/{manager_id}', [ManagerDashboardController::class, 'show'])->name('manager_dashboard'); 
     });

  Route::get('/callbacks/{user_id?}', [CallbackController::class, 'index'])->name('callbacklist');
  Route::post('/callbacks/save', [CallbackController::class, 'save'])->name('callbacks.save');
  Route::post('/callbacks/delete', [CallbackController::class, 'delete'])->name('callbacks.delete');
  ?>