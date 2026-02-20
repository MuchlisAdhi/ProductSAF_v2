<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductAdminController extends Controller
{
    /**
     * Available sack colors.
     *
     * @var list<string>
     */
    private array $sackColors = ['Merah', 'Biru', 'Hijau', 'Orange', 'Pink'];

    /**
     * Product list.
     */
    public function index(Request $request): View
    {
        $role = Auth::user()->role;
        $query = trim((string) $request->query('q', ''));
        $categoryFilter = trim((string) $request->query('category', ''));
        $sackColorFilter = trim((string) $request->query('sackColor', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 10), [5, 10, 20, 50, 100], 10);

        $builder = Product::query()->with(['category'])->withCount('nutritions');

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('sack_color', 'like', "%{$query}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($query): void {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    });
            });
        }

        if ($categoryFilter !== '') {
            $builder->where('category_id', $categoryFilter);
        }

        if ($sackColorFilter !== '') {
            $builder->where('sack_color', $sackColorFilter);
        }

        $filteredCount = (clone $builder)->count();
        $products = $builder->orderByDesc('created_at')->paginate($pageSize)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'query' => $query,
            'categoryFilter' => $categoryFilter,
            'sackColorFilter' => $sackColorFilter,
            'categoryOptions' => Category::query()->orderBy('order_number')->orderBy('name')->get(['id', 'name']),
            'sackColorOptions' => Product::query()->select('sack_color')->distinct()->orderBy('sack_color')->pluck('sack_color'),
            'pageSize' => $pageSize,
            'totalCount' => Product::query()->count(),
            'filteredCount' => $filteredCount,
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Show product create form.
     */
    public function create(): View
    {
        $role = Auth::user()->role;

        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('order_number')->orderBy('name')->get(),
            'sackColors' => $this->sackColors,
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Persist product.
     */
    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        DB::transaction(function () use ($request, $payload): void {
            if ($request->hasFile('image')) {
                $payload['image_id'] = $this->storeUploadedAsset($request);
            }

            /** @var Product $product */
            $product = Product::query()->create($payload);
            $this->syncNutritions($product, (array) $request->input('nutritions'));
        });

        return redirect()->route('admin.products.index')->with('success', 'Product has been created.');
    }

    /**
     * Show product edit form.
     */
    public function edit(string $id): View
    {
        $role = Auth::user()->role;

        return view('admin.products.edit', [
            'product' => Product::query()->with(['nutritions', 'image'])->findOrFail($id),
            'categories' => Category::query()->orderBy('order_number')->orderBy('name')->get(),
            'sackColors' => $this->sackColors,
            'roleLabel' => $role instanceof Role ? $role->value : (string) $role,
        ]);
    }

    /**
     * Update product.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);
        $payload = $this->validatePayload($request, $product->id);

        DB::transaction(function () use ($request, $payload, $product): void {
            if ($request->boolean('remove_image')) {
                $payload['image_id'] = null;
            }

            if ($request->hasFile('image')) {
                $payload['image_id'] = $this->storeUploadedAsset($request);
            }

            $product->update($payload);
            $product->nutritions()->delete();
            $this->syncNutritions($product, (array) $request->input('nutritions'));
        });

        return redirect()->route('admin.products.index')->with('success', 'Product has been updated.');
    }

    /**
     * Delete one product.
     */
    public function destroy(string $id): RedirectResponse
    {
        Product::query()->findOrFail($id)->delete();

        return back()->with('success', 'Product deleted.');
    }

    /**
     * Delete many products.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ], [
            'ids.min' => 'Select at least one product',
        ]);

        $count = Product::query()->whereIn('id', $payload['ids'])->delete();

        return back()->with('success', "{$count} products deleted.");
    }

    /**
     * Validate product payload.
     *
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', Rule::unique('products', 'code')->ignore($id)],
            'name' => ['required', 'string', 'min:2'],
            'description' => ['required', 'string', 'min:4'],
            'sack_color' => ['required', 'string', 'min:2'],
            'category_id' => ['required', 'string', Rule::exists('categories', 'id')],
            'nutritions' => ['required', 'array', 'min:1'],
            'nutritions.*.label' => ['required', 'string'],
            'nutritions.*.value' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Code is required',
            'name.min' => 'Name is required',
            'description.min' => 'Description is required',
            'sack_color.min' => 'Sack color is required',
            'nutritions.min' => 'At least one nutrition item is required',
        ]);
    }

    /**
     * Save uploaded image and metadata.
     */
    private function storeUploadedAsset(Request $request): string
    {
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = now()->timestamp.'-'.Str::uuid().'.'.$extension;
        $originalFileName = (string) $file->getClientOriginalName();
        $mimeType = (string) ($file->getClientMimeType() ?: $file->getMimeType() ?: 'application/octet-stream');
        $size = (int) ($file->getSize() ?? 0);

        if ($size <= 0) {
            $realPath = $file->getRealPath();
            if (is_string($realPath) && $realPath !== '' && is_file($realPath)) {
                $resolvedSize = @filesize($realPath);
                $size = is_int($resolvedSize) ? $resolvedSize : 0;
            }
        }

        $destination = public_path('uploads');

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $asset = Asset::query()->create([
            'original_file_name' => $originalFileName !== '' ? $originalFileName : $filename,
            'system_path' => '/uploads/'.$filename,
            'mime_type' => $mimeType,
            'size' => $size,
        ]);

        return $asset->id;
    }

    /**
     * Create nutrition rows.
     *
     * @param  list<array<string, mixed>>  $nutritions
     */
    private function syncNutritions(Product $product, array $nutritions): void
    {
        $rows = collect($nutritions)
            ->map(function (array $nutrition): array {
                return [
                    'label' => trim((string) ($nutrition['label'] ?? '')),
                    'value' => trim((string) ($nutrition['value'] ?? '')),
                ];
            })
            ->filter(fn (array $nutrition): bool => $nutrition['label'] !== '' && $nutrition['value'] !== '')
            ->values()
            ->all();

        $product->nutritions()->createMany($rows);
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
