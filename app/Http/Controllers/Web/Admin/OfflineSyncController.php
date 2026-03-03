<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Product;
use App\Support\ImageOptimizer;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfflineSyncController extends Controller
{
    /**
     * Persist queued category from offline client.
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', Rule::unique('categories', 'name')],
            'icon' => ['required', 'string', 'min:1'],
            'order_number' => ['required', 'integer', 'min:0'],
        ], [
            'name.min' => 'Category name is required',
            'icon.min' => 'Icon name is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            /** @var Category $category */
            $category = Category::query()->create($validator->validated());
            $this->clearCategoryCaches();
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'error' => 'Category name already exists',
                ], 409);
            }

            report($exception);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ], 201);
    }

    /**
     * Update category from queued offline client payload.
     */
    public function updateCategory(Request $request, string $id): JsonResponse
    {
        $category = Category::query()->find($id);
        if (! $category) {
            return response()->json([
                'success' => false,
                'error' => 'Category not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', Rule::unique('categories', 'name')->ignore($category->id)],
            'icon' => ['required', 'string', 'min:1'],
            'order_number' => ['required', 'integer', 'min:0'],
        ], [
            'name.min' => 'Category name is required',
            'icon.min' => 'Icon name is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category->update($validator->validated());
            $this->clearCategoryCaches();
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'error' => 'Category name already exists',
                ], 409);
            }

            report($exception);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
    }

    /**
     * Delete category from queued offline client payload.
     */
    public function deleteCategory(string $id): JsonResponse
    {
        $category = Category::query()->find($id);
        if (! $category) {
            return response()->json([
                'success' => false,
                'error' => 'Category not found',
            ], 404);
        }

        try {
            $category->delete();
            $this->clearCategoryCaches();
            $this->clearProductCaches($id);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'error' => 'Category is used by products and cannot be deleted',
                ], 409);
            }

            report($exception);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Persist queued product from offline client.
     */
    public function storeProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'min:2'],
            'description' => ['required', 'string', 'min:4'],
            'sack_color' => ['required', 'string', 'min:2'],
            'category_id' => ['required', 'string', Rule::exists('categories', 'id')],
            'nutritions' => ['required', 'array', 'min:1'],
            'nutritions.*.label' => ['required', 'string'],
            'nutritions.*.value' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:10240'],
        ], [
            'code.required' => 'Code is required',
            'name.min' => 'Name is required',
            'description.min' => 'Description is required',
            'sack_color.min' => 'Sack color is required',
            'nutritions.min' => 'At least one nutrition item is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $payload['sack_color'] = $this->normalizeSackColor((string) $payload['sack_color']);

        try {
            $product = DB::transaction(function () use ($request, $payload): Product {
                if ($request->hasFile('image')) {
                    $payload['image_id'] = $this->storeUploadedAsset($request);
                }

                $rows = collect((array) ($payload['nutritions'] ?? []))
                    ->map(function (array $nutrition): array {
                        return [
                            'label' => trim((string) ($nutrition['label'] ?? '')),
                            'value' => trim((string) ($nutrition['value'] ?? '')),
                        ];
                    })
                    ->filter(fn (array $nutrition): bool => $nutrition['label'] !== '' && $nutrition['value'] !== '')
                    ->values()
                    ->all();

                if ($rows === []) {
                    throw new \RuntimeException('At least one nutrition item is required');
                }

                unset($payload['nutritions']);

                /** @var Product $product */
                $product = Product::query()->create($payload);
                $product->nutritions()->createMany($rows);

                return $product;
            });
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }

        $this->clearProductCaches((string) $product->category_id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
            ],
        ], 201);
    }

    /**
     * Update product from queued offline client payload.
     */
    public function updateProduct(Request $request, string $id): JsonResponse
    {
        $product = Product::query()->find($id);
        if (! $product) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string'],
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $payload['sack_color'] = $this->normalizeSackColor((string) $payload['sack_color']);
        $oldCategoryId = (string) $product->category_id;

        try {
            $product = DB::transaction(function () use ($request, $payload, $product): Product {
                if ($request->boolean('remove_image')) {
                    $payload['image_id'] = null;
                }

                if ($request->hasFile('image')) {
                    $payload['image_id'] = $this->storeUploadedAsset($request);
                }

                $rows = collect((array) ($payload['nutritions'] ?? []))
                    ->map(function (array $nutrition): array {
                        return [
                            'label' => trim((string) ($nutrition['label'] ?? '')),
                            'value' => trim((string) ($nutrition['value'] ?? '')),
                        ];
                    })
                    ->filter(fn (array $nutrition): bool => $nutrition['label'] !== '' && $nutrition['value'] !== '')
                    ->values()
                    ->all();

                if ($rows === []) {
                    throw new \RuntimeException('At least one nutrition item is required');
                }

                unset($payload['nutritions'], $payload['remove_image']);

                $product->update($payload);
                $product->nutritions()->delete();
                $product->nutritions()->createMany($rows);

                return $product->fresh();
            });
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }

        $newCategoryId = (string) $product->category_id;
        $this->clearProductCaches($oldCategoryId);
        if ($newCategoryId !== $oldCategoryId) {
            $this->clearProductCaches($newCategoryId);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
            ],
        ]);
    }

    /**
     * Delete product from queued offline client payload.
     */
    public function deleteProduct(string $id): JsonResponse
    {
        $product = Product::query()->find($id);
        if (! $product) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
            ], 404);
        }

        $categoryId = (string) $product->category_id;

        try {
            $product->delete();
            $this->clearProductCaches($categoryId);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Save uploaded image and metadata.
     */
    private function storeUploadedAsset(Request $request): string
    {
        $file = $request->file('image');
        $optimized = (new ImageOptimizer())->store($file, 'uploads');

        $asset = Asset::query()->create([
            'original_file_name' => $optimized['original_file_name'],
            'system_path' => $optimized['system_path'],
            'thumbnail_path' => $optimized['thumbnail_path'],
            'mime_type' => $optimized['mime_type'],
            'size' => $optimized['size'],
        ]);

        return $asset->id;
    }

    /**
     * Normalize legacy sack color labels.
     */
    private function normalizeSackColor(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'orange', 'oranye' => 'Oranye',
            'pink', 'merah muda' => 'Merah Muda',
            default => trim($value),
        };
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
            'admin.dashboard.categories.total_count',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }

    /**
     * Clear cache keys used by catalog and admin product listings.
     */
    private function clearProductCaches(string $categoryId): void
    {
        foreach ([
            'admin.products.categories',
            'admin.products.sack_colors',
            'admin.products.total_count',
            'catalog.home.categories',
            'catalog.products.categories',
            'catalog.products.sack_colors',
            'catalog.products.total_count',
            'admin.dashboard.products.total_count',
            'catalog.category.'.$categoryId.'.total_count',
            'catalog.category.'.$categoryId.'.sack_colors',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}
