<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('pathi:admin', function () {
    $email = $this->ask('Admin email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Valid email required.');

        return 1;
    } if (User::where('email', $email)->exists()) {
        $this->error('Account already exists; no changes made.');

        return 1;
    } $password = $this->secret('Password (at least 12 characters)');
    if (strlen($password ?? '') < 12) {
        $this->error('Use at least 12 characters.');

        return 1;
    } User::create(['name' => 'Pathi', 'email' => $email, 'password' => Hash::make($password), 'is_admin' => true]);
    $this->info('Admin created. No public registration is enabled.');
})->purpose('Securely create an administrator');
