<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackPublicVisit
{
    private const TRACKER_TABLE = 'tracker_visits';

    private static ?bool $trackerTableExists = null;

    /**
     * Track public page visits.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        $this->recordVisit($request);

        return $response;
    }

    /**
     * Decide if request should be tracked.
     */
    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! config('public_tracker.enabled', true)) {
            return false;
        }

        if (! $request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        $path = ltrim($request->path(), '/');

        if ($path === '') {
            return true;
        }

        $excludedPaths = (array) config('public_tracker.excluded_paths', []);
        if ($excludedPaths !== [] && Str::is($excludedPaths, $path)) {
            return false;
        }

        if (preg_match('/\.(?:css|js|map|xml|ico|gif|jpe?g|png|svg|webp|avif|woff2?|ttf)$/i', $path) === 1) {
            return false;
        }

        return true;
    }

    /**
     * Persist visit row into local tracker table.
     */
    private function recordVisit(Request $request): void
    {
        if (! $this->trackerTableExists()) {
            return;
        }

        try {
            $userAgent = trim((string) $request->userAgent());
            $sessionId = $request->hasSession() ? (string) $request->session()->getId() : '';
            $ipAddress = (string) $request->ip();
            $userId = (string) ($request->user()?->getAuthIdentifier() ?? '');
            $visitorHash = hash('sha256', implode('|', [$sessionId, $ipAddress, Str::lower($userAgent), $userId]));

            DB::table(self::TRACKER_TABLE)->insert([
                'session_id' => $sessionId !== '' ? $sessionId : null,
                'visitor_hash' => $visitorHash,
                'user_id' => $userId !== '' ? $userId : null,
                'is_guest' => $userId === '',
                'ip_address' => $ipAddress !== '' ? $ipAddress : null,
                'user_agent' => $userAgent !== '' ? Str::limit($userAgent, 1024, '') : null,
                'method' => (string) $request->method(),
                'path' => '/'.ltrim((string) $request->path(), '/'),
                'full_url' => Str::limit((string) $request->fullUrl(), 2048, ''),
                'referer' => Str::limit((string) ($request->headers->get('referer') ?? ''), 2048, ''),
                'visited_at' => now(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Resolve tracker table once per request lifecycle.
     */
    private function trackerTableExists(): bool
    {
        if (self::$trackerTableExists !== null) {
            return self::$trackerTableExists;
        }

        try {
            self::$trackerTableExists = Schema::hasTable(self::TRACKER_TABLE);
        } catch (Throwable) {
            self::$trackerTableExists = false;
        }

        return self::$trackerTableExists;
    }
}
