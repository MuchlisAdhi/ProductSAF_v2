<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthPageController extends Controller
{
    /**
     * Render login page.
     */
    public function loginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to('/admin');
        }

        return view('auth.login', [
            'nextPath' => (string) $request->query('next', '/admin'),
        ]);
    }

    /**
     * Handle regular web login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'next' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $ok = Auth::attempt([
            'email' => strtolower((string) $request->string('email')),
            'password' => (string) $request->string('password'),
        ]);

        if (! $ok) {
            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $next = (string) $request->string('next', '/admin');
        $safeNext = str_starts_with($next, '/') && ! str_starts_with($next, '//') ? $next : '/admin';

        return redirect()->to($safeNext);
    }

    /**
     * Handle web logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
