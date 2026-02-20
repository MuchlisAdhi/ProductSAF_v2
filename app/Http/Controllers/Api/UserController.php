<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    /**
     * List users (SUPERADMIN only through middleware).
     */
    public function index()
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $users->map(fn (User $user) => $this->mapUser($user))->values(),
        ]);
    }

    /**
     * Create user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', new Enum(Role::class)],
        ], [
            'name.min' => 'Name must be at least 2 characters',
            'email.email' => 'Email is invalid',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $user = User::query()->create([
                'name' => (string) $request->string('name'),
                'email' => strtolower((string) $request->string('email')),
                'password' => (string) $request->string('password'),
                'role' => $request->filled('role') ? (string) $request->string('role') : Role::USER,
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json(['error' => 'Email already exists'], 409);
            }

            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json([
            'data' => $this->mapUser($user),
        ], 201);
    }

    /**
     * Update user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::query()->find($id);
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', new Enum(Role::class)],
        ], [
            'name.min' => 'Name must be at least 2 characters',
            'email.email' => 'Email is invalid',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $payload = [
                'name' => (string) $request->string('name'),
                'email' => strtolower((string) $request->string('email')),
                'role' => (string) $request->string('role'),
            ];

            $password = (string) $request->string('password');
            if ($password !== '') {
                $payload['password'] = $password;
            }

            $user->update($payload);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json(['error' => 'Email already exists'], 409);
            }

            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json([
            'data' => $this->mapUser($user->fresh()),
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(string $id)
    {
        $user = User::query()->find($id);
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ((string) Auth::id() === $id) {
            return response()->json(['error' => 'You cannot delete your own account'], 400);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUser(User $user): array
    {
        $role = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'createdAt' => optional($user->created_at)->toISOString(),
            'updatedAt' => optional($user->updated_at)->toISOString(),
        ];
    }
}
