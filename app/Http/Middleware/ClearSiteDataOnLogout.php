<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The installable app saves pages for offline use, and admin/account pages contain
 * customer details. On logout, tell the browser to delete those saved copies.
 */
class ClearSiteDataOnLogout
{
    private const LOGOUT_ROUTES = ['logout', 'filament.admin.auth.logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('post') && $request->routeIs(...self::LOGOUT_ROUTES)) {
            $response->headers->set('Clear-Site-Data', '"cache", "storage"');
        }

        return $response;
    }
}
