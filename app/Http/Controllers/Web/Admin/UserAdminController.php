<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    /**
     * User list page.
     */
    public function index(Request $request): View
    {
        $role = Auth::user()->role;
        $query = trim((string) $request->query('q', ''));
        $roleFilter = trim((string) $request->query('role', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 10), [5, 10, 20, 50, 100], 10);

        $builder = User::query();
        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });
        }

        if (in_array($roleFilter, Role::values(), true)) {
            $builder->where('role', $roleFilter);
        }

        $filteredCount = (clone $builder)->count();
        $users = $builder->orderByDesc('created_at')->paginate($pageSize)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'query' => $query,
            'roleFilter' => $roleFilter,
            'pageSize' => $pageSize,
            'totalCount' => User::query()->count(),
            'filteredCount' => $filteredCount,
            'roles' => Role::values(),
            'currentUserId' => (string) Auth::id(),
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Persist user.
     */
    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', new Enum(Role::class)],
        ]);

        User::query()->create([
            'name' => $payload['name'],
            'email' => strtolower($payload['email']),
            'password' => $payload['password'],
            'role' => $payload['role'],
        ]);

        return back()->with('success', 'User created.');
    }

    /**
     * Render edit page.
     */
    public function edit(string $id): View
    {
        $role = Auth::user()->role;

        return view('admin.users.edit', [
            'targetUser' => User::query()->findOrFail($id),
            'roles' => Role::values(),
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Update existing user.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $targetUser = User::query()->findOrFail($id);

        $payload = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email', (new Unique('users', 'email'))->ignore($targetUser->id, 'id')],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', new Enum(Role::class)],
        ]);

        $targetUser->name = $payload['name'];
        $targetUser->email = strtolower($payload['email']);
        $targetUser->role = $payload['role'];
        if (! empty($payload['password'])) {
            $targetUser->password = $payload['password'];
        }
        $targetUser->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    /**
     * Delete existing user.
     */
    public function destroy(string $id): RedirectResponse
    {
        if ((string) Auth::id() === $id) {
            return back()->withErrors(['error' => 'You cannot delete your own account']);
        }

        User::query()->findOrFail($id)->delete();

        return back()->with('success', 'User deleted.');
    }

    /**
     * Resolve allowed page size.
     *
     * @param  list<int>  $allowed
     */
    private function resolvePageSize(int $requested, array $allowed, int $default): int
    {
        return in_array($requested, $allowed, true) ? $requested : $default;
    }
}
