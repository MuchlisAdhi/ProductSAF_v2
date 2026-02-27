<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManualMaintenanceMode
{
    /**
     * Handle manual maintenance mode using public/maintenance.enable marker file.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        if (! is_file(public_path('maintenance.enable'))) {
            return $next($request);
        }

        if ($this->isBypassedPath($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Aplikasi sedang dalam maintenance.',
            ], 503, ['Retry-After' => '120']);
        }

        return response()
            ->view('errors.503', [], 503)
            ->header('Retry-After', '120');
    }

    /**
     * Paths that should still be accessible while maintenance mode is active.
     */
    private function isBypassedPath(Request $request): bool
    {
        return $request->is('admin*')
            || $request->is('login')
            || $request->is('logout')
            || $request->is('up')
            || $request->is('vendor/*')
            || $request->is('build/*')
            || $request->is('images/*')
            || $request->is('uploads/*')
            || $request->is('storage/*')
            || $request->is('favicon.ico')
            || $request->is('robots.txt');
    }
}
