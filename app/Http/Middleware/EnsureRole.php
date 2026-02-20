<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect()->route('login', ['next' => $request->path()]);
        }

        $currentRole = Auth::user()->role;
        $normalizedRole = $currentRole instanceof Role ? $currentRole->value : (string) $currentRole;

        if (! in_array($normalizedRole, $roles, true)) {
            if ($request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect()->to('/admin');
        }

        return $next($request);
    }
}
