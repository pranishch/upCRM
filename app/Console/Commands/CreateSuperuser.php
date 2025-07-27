<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperuser extends Command
{
    protected $signature = 'make:superuser';
    protected $description = 'Create a superuser account';

    public function handle()
    {
        $username = $this->ask('Enter username');
        $email = $this->ask('Enter email');
        $password = $this->secret('Enter password');
        $password_confirmation = $this->secret('Confirm password');

        if ($password !== $password_confirmation) {
            $this->error('Passwords do not match.');
            return 1;
        }

        if (User::where('email', $email)->orWhere('username', $username)->exists()) {
            $this->error('Username or email already exists.');
            return 1;
        }

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
            'is_superuser' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        $this->info('Superuser created successfully.');
        return 0;
    }
}