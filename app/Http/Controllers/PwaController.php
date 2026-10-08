<?php

namespace App\Http\Controllers;

use Composer\InstalledVersions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

/**
 * Service worker + offline page for the installable app (shop and admin).
 */
class PwaController extends Controller
{
    /**
     * The service worker is rendered so its version changes on every deploy that ships new
     * CSS/JS (or a new Filament release). A new version makes installed apps show "Update".
     */
    public function serviceWorker(): Response
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = File::exists($manifestPath) ? json_decode(File::get($manifestPath), true) : [];

        // Precache the app shell: compiled CSS/JS and the main font files.
        $assets = collect($manifest)
            ->flatMap(fn (array $entry) => [$entry['file'] ?? null, ...($entry['css'] ?? [])])
            ->filter(fn (?string $file) => $file && preg_match('/\.(css|js|woff2)$/', $file))
            ->map(fn (string $file) => '/build/'.$file)
            ->unique()
            ->values()
            ->all();

        $version = substr(md5(implode('|', [
            File::exists($manifestPath) ? md5_file($manifestPath) : 'dev',
            md5_file(resource_path('views/pwa/sw.blade.php')),
            md5_file(public_path('pwa.js')),
            class_exists(InstalledVersions::class) ? InstalledVersions::getVersion('filament/filament') : '',
        ])), 0, 12);

        return response()
            ->view('pwa.sw', [
                'version' => $version,
                'precache' => ['/offline', '/pwa.js', '/site.webmanifest', '/icons/icon-192.png', ...$assets],
            ])
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            // Always revalidate so phones see new versions right away.
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Service-Worker-Allowed', '/');
    }

    public function offline()
    {
        return response()->view('pwa.offline')->header('Cache-Control', 'no-cache');
    }
}
