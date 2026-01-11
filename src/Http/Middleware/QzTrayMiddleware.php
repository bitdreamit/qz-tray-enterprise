<?php

namespace BitDreamIT\QzTray\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QzTrayMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if QZ Tray is enabled
        if (!config('qz-tray.enabled', true)) {
            Log::warning('QZ Tray access attempted while disabled', [
                'ip' => $request->ip(),
                'user' => Auth::id(),
                'url' => $request->url(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'QZ Tray is disabled',
            ], 403);
        }

        // Check HTTPS requirement
        if (config('qz-tray.security.require_https', true) &&
            !$request->secure() &&
            !$this->isLocalhost($request)) {

            Log::warning('QZ Tray access attempted without HTTPS', [
                'ip' => $request->ip(),
                'secure' => $request->secure(),
                'url' => $request->url(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'HTTPS is required for QZ Tray',
            ], 403);
        }

        // Check authentication if required
        if (in_array('auth', config('qz-tray.middleware', [])) && !Auth::check()) {
            Log::warning('Unauthenticated QZ Tray access attempt', [
                'ip' => $request->ip(),
                'url' => $request->url(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Authentication required',
            ], 401);
        }

        // Check user permissions
        if (Auth::check() && !$this->hasPermission(Auth::user())) {
            Log::warning('Unauthorized QZ Tray access attempt', [
                'user' => Auth::id(),
                'ip' => $request->ip(),
                'url' => $request->url(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Insufficient permissions',
            ], 403);
        }

        // Add security headers
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-XSS-Protection', '1; mode=block');

            // CSP header for QZ Tray
            $response->header('Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
                "connect-src 'self' wss://localhost:8181 wss://localhost:8182; " .
                "style-src 'self' 'unsafe-inline'; " .
                "img-src 'self' data: https:;"
            );
        }

        return $response;
    }

    /**
     * Check if request is from localhost
     */
    protected function isLocalhost(Request $request): bool
    {
        if (!config('qz-tray.security.allow_localhost', true)) {
            return false;
        }

        $ip = $request->ip();
        $host = $request->getHost();

        $localhosts = array_merge(
            ['127.0.0.1', '::1', 'localhost'],
            config('qz-tray.advanced.trusted_domains', [])
        );

        return in_array($ip, $localhosts) || in_array($host, $localhosts);
    }

    /**
     * Check if user has permission to access QZ Tray
     */
    protected function hasPermission($user): bool
    {
        // Default: all authenticated users have access
        // Override this method in your application for custom permissions

        // Example: Check for specific role/permission
        // return $user->hasRole('admin') || $user->can('use-qz-tray');

        return true;
    }
}
