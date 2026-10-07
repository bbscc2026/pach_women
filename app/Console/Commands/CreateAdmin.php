<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Create or update an admin login for /admin. The password is typed in the terminal (hidden).
 *
 *   php artisan shop:admin you@example.com
 */
#[Signature('shop:admin {email : Admin email address} {--name=PACH Admin : Display name}')]
#[Description('Create an admin account for /admin, or reset an existing admin password')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (at least 8 characters, hidden while typing)');
        $confirm = (string) $this->secret('Type the password again');

        if (strlen($password) < 8 || $password !== $confirm) {
            $this->error($password !== $confirm ? 'The passwords did not match.' : 'Use at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->exists ? $user->name : $this->option('name');
        $user->password = $password;
        $user->is_admin = true;
        $user->save();

        $this->info(($user->wasRecentlyCreated ? 'Admin created' : 'Admin updated').": {$email}. Log in at ".url('/admin'));

        return self::SUCCESS;
    }
}
