<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Authenticate and start a web session.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.email' => 'Email is invalid',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $credentials = [
            'email' => strtolower((string) $request->string('email')),
            'password' => (string) $request->string('password'),
        ];

        if (! Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        return response()->json($this->mapUser($user));
    }

    /**
     * Register a new user as USER role.
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.min' => 'Name must be at least 2 characters',
            'email.email' => 'Email is invalid',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $user = User::create([
                'name' => (string) $request->string('name'),
                'email' => strtolower((string) $request->string('email')),
                'password' => (string) $request->string('password'),
                'role' => Role::USER,
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json(['error' => 'Email is already registered'], 409);
            }

            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json($this->mapUser($user));
    }

    /**
     * End the current auth session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, string>
     */
    private function mapUser(User $user): array
    {
        $role = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'name' => $user->name,
        ];
    }
}
