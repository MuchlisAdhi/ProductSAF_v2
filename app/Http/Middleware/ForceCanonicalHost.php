<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCanonicalHost
{
    /**
     * Redirect non-canonical host requests to the configured canonical host.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = trim((string) config('app.canonical_host', ''));
        if ($canonicalHost === '') {
            return $next($request);
        }

        $requestHost = strtolower($request->getHost());
        if ($requestHost === strtolower($canonicalHost)) {
            return $next($request);
        }

        $scheme = $request->isSecure() ? 'https' : $request->getScheme();
        $target = $scheme.'://'.$canonicalHost.$request->getRequestUri();

        return redirect()->to($target, 301);
    }
}
