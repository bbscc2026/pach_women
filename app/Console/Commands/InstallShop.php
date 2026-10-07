<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * First-time server setup, run once in an interactive SSH session:
 *
 *   php artisan shop:install
 *
 * Asks for the MySQL password (hidden) and saves it to .env, then migrates, seeds the
 * sample catalogue, links storage, caches config and creates the admin login.
 */
#[Signature('shop:install {--no-seed : Skip the sample products, banners and pages}')]
#[Description('First-time production setup: database password, tables, sample data, admin login')]
class InstallShop extends Command
{
    public function handle(): int
    {
        $this->info('PACH WOMEN — first-time setup');

        if (! $this->connectDatabase()) {
            return self::FAILURE;
        }

        $this->components->task('Creating database tables', fn () => Artisan::call('migrate', ['--force' => true]) === 0);

        if (! $this->option('no-seed')) {
            $this->components->task('Adding sample products, banners and pages', fn () => Artisan::call('db:seed', ['--force' => true]) === 0);
        }

        $this->components->task('Linking image storage', function () {
            if (! file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            return file_exists(public_path('storage'));
        });

        $this->newLine();
        $this->info('Now choose your admin login for /admin.');
        $email = $this->ask('Admin email', env('ADMIN_EMAIL', 'admin@pachwomen.com'));

        if ($this->call('shop:admin', ['email' => $email]) !== self::SUCCESS) {
            $this->warn('Admin not created. Run again later: php artisan shop:admin '.$email);
        }

        $this->components->task('Caching for speed', fn () => Artisan::call('optimize') === 0 && Artisan::call('icons:cache') === 0);

        $this->newLine();
        $this->info('Done. Shop: '.config('app.url').'   Admin: '.config('app.url').'/admin');

        return self::SUCCESS;
    }

    private function connectDatabase(): bool
    {
        if ($this->canConnect()) {
            $this->components->info('Database connection works.');

            return true;
        }

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $password = (string) $this->secret('MySQL password for '.config('database.connections.mysql.username').' (hidden while typing)');

            $this->writeEnv('DB_PASSWORD', $password);
            config(['database.connections.mysql.password' => $password]);
            DB::purge('mysql');

            if ($this->canConnect()) {
                $this->components->info('Database connection works. Password saved to .env.');

                return true;
            }

            $this->error('Could not connect with that password. Try again.');
        }

        $this->error('Giving up. Check the database name, username and password in hPanel → Databases.');

        return false;
    }

    private function canConnect(): bool
    {
        try {
            DB::connection('mysql')->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Set KEY=value in .env, quoting so any characters in the value are kept literally.
     */
    private function writeEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        $quoted = str_contains($value, "'")
            ? '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"'
            : "'".$value."'";

        $env = file_get_contents($path);
        $line = $key.'='.$quoted;

        $env = preg_match("/^{$key}=.*$/m", $env)
            ? preg_replace_callback("/^{$key}=.*$/m", fn () => $line, $env)
            : rtrim($env).PHP_EOL.$line.PHP_EOL;

        file_put_contents($path, $env);
    }
}
