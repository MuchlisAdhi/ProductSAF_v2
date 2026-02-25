<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryAdminController extends Controller
{
    /**
     * Category list page.
     */
    public function index(Request $request): View
    {
        $lucideIcons = $this->lucideIcons();
        $role = Auth::user()->role;
        $query = trim((string) $request->query('q', ''));
        $iconFilter = trim((string) $request->query('icon', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 10), [5, 10, 20, 50, 100], 10);

        $builder = Category::query()
            ->select(['id', 'name', 'icon', 'order_number'])
            ->withCount('products');

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('icon', 'like', "%{$query}%");

                if (ctype_digit($query)) {
                    $q->orWhere('order_number', (int) $query);
                }
            });
        }

        if ($iconFilter !== '') {
            $builder->where('icon', $iconFilter);
        }

        $filteredCount = (clone $builder)->count();
        $categories = $builder
            ->orderBy('order_number')
            ->orderBy('name')
            ->paginate($pageSize)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'query' => $query,
            'iconFilter' => $iconFilter,
            'iconOptions' => Cache::remember(
                'admin.categories.icon_options',
                now()->addMinutes(10),
                fn () => Category::query()
                    ->select('icon')
                    ->distinct()
                    ->orderBy('icon')
                    ->pluck('icon')
            ),
            'lucideIcons' => $lucideIcons,
            'pageSize' => $pageSize,
            'totalCount' => Cache::remember(
                'admin.categories.total_count',
                now()->addMinutes(10),
                fn () => Category::query()->count()
            ),
            'filteredCount' => $filteredCount,
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Show create category page.
     */
    public function create(): View
    {
        $role = Auth::user()->role;

        return view('admin.categories.create', [
            'lucideIcons' => $this->lucideIcons(),
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Persist category.
     */
    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);
        Category::query()->create($payload);
        $this->clearCategoryCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    /**
     * Show edit form.
     */
    public function edit(string $id): View
    {
        $lucideIcons = $this->lucideIcons();
        $role = Auth::user()->role;

        return view('admin.categories.edit', [
            'category' => Category::query()->findOrFail($id),
            'lucideIcons' => $lucideIcons,
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Update category.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);
        $payload = $this->validatePayload($request, $category->id);
        $category->update($payload);
        $this->clearCategoryCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    /**
     * Delete category.
     */
    public function destroy(string $id): RedirectResponse
    {
        $category = Category::query()->findOrFail($id);

        try {
            $category->delete();
            $this->clearCategoryCaches();
        } catch (QueryException $exception) {
            return back()->withErrors([
                'error' => 'Category is used by products and cannot be deleted',
            ]);
        }

        return back()->with('success', 'Category deleted.');
    }

    /**
     * Resolve Lucide svg file for icon modal preview.
     */
    public function iconSvg(string $name): Response
    {
        $normalizedName = Str::of($name)
            ->lower()
            ->replace(['_', '.'], '-')
            ->replaceMatches('/[^a-z0-9\-]/', '')
            ->trim('-')
            ->toString();

        if ($normalizedName === '') {
            abort(404);
        }

        $iconPath = base_path("vendor/mallardduck/blade-lucide-icons/resources/svg/{$normalizedName}.svg");

        if (! File::exists($iconPath)) {
            abort(404);
        }

        return response(File::get($iconPath), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Validate category input.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'min:2', Rule::unique('categories', 'name')->ignore($id)],
            'icon' => ['required', 'string', 'min:1'],
            'order_number' => ['required', 'integer', 'min:0'],
        ], [
            'name.min' => 'Category name is required',
            'icon.min' => 'Icon name is required',
        ]);
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

    /**
     * Resolve full Lucide icon list from installed package.
     *
     * @return Collection<int, string>
     */
    private function lucideIcons(): Collection
    {
        $iconDirectory = base_path('vendor/mallardduck/blade-lucide-icons/resources/svg');

        if (! File::isDirectory($iconDirectory)) {
            return collect(['box']);
        }

        return Cache::remember('admin.categories.lucide_icons', now()->addHours(12), function () use ($iconDirectory) {
            return collect(File::files($iconDirectory))
                ->filter(fn ($file): bool => $file->getExtension() === 'svg')
                ->map(fn ($file): string => Str::lower((string) $file->getFilenameWithoutExtension()))
                ->unique()
                ->sort()
                ->values();
        });
    }

    /**
     * Clear category-related cache keys.
     */
    private function clearCategoryCaches(): void
    {
        foreach ([
            'admin.categories.icon_options',
            'admin.categories.total_count',
            'catalog.home.categories',
            'catalog.products.categories',
            'admin.products.categories',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}
