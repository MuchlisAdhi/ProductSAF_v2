<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * List all products for API.
     */
    public function index()
    {
        $products = Product::query()
            ->with(['category', 'nutritions', 'image'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $products->map(fn (Product $product) => $this->mapProduct($product)),
        ]);
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        $validator = $this->validateProductRequest($request);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $category = Category::query()->find((string) $request->string('categoryId'));
        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $imageId = $request->string('imageId')->value();
        if ($imageId !== '' && ! Asset::query()->whereKey($imageId)->exists()) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        try {
            $product = DB::transaction(function () use ($request, $imageId): Product {
                /** @var Product $product */
                $product = Product::query()->create([
                    'code' => (string) $request->string('code'),
                    'name' => (string) $request->string('name'),
                    'description' => (string) $request->string('description'),
                    'sack_color' => $this->normalizeSackColor((string) $request->string('sackColor')),
                    'category_id' => (string) $request->string('categoryId'),
                    'image_id' => $imageId !== '' ? $imageId : null,
                ]);

                $nutritions = collect((array) $request->input('nutritions'))
                    ->map(fn (array $item) => [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'value' => trim((string) ($item['value'] ?? '')),
                    ])
                    ->all();

                $product->nutritions()->createMany($nutritions);

                return $product->fresh(['category', 'nutritions', 'image']);
            });
        } catch (QueryException $exception) {
            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json([
            'data' => $this->mapProduct($product),
        ], 201);
    }

    /**
     * Show single product.
     */
    public function show(string $id)
    {
        $product = Product::query()
            ->with(['category', 'nutritions', 'image'])
            ->find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'data' => $this->mapProduct($product),
        ]);
    }

    /**
     * Update product.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::query()->find($id);
        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validator = $this->validateProductRequest($request);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $category = Category::query()->find((string) $request->string('categoryId'));
        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $imageId = $request->string('imageId')->value();
        if ($imageId !== '' && ! Asset::query()->whereKey($imageId)->exists()) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        try {
            $product = DB::transaction(function () use ($request, $product, $imageId): Product {
                $product->update([
                    'code' => (string) $request->string('code'),
                    'name' => (string) $request->string('name'),
                    'description' => (string) $request->string('description'),
                    'sack_color' => $this->normalizeSackColor((string) $request->string('sackColor')),
                    'category_id' => (string) $request->string('categoryId'),
                    'image_id' => $imageId !== '' ? $imageId : null,
                ]);

                $product->nutritions()->delete();
                $nutritions = collect((array) $request->input('nutritions'))
                    ->map(fn (array $item) => [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'value' => trim((string) ($item['value'] ?? '')),
                    ])
                    ->all();
                $product->nutritions()->createMany($nutritions);

                return $product->fresh(['category', 'nutritions', 'image']);
            });
        } catch (QueryException $exception) {
            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json([
            'data' => $this->mapProduct($product),
        ]);
    }

    /**
     * Delete product by id.
     */
    public function destroy(string $id)
    {
        $product = Product::query()->find($id);
        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete products by list of ids.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ], [
            'ids.min' => 'Select at least one product',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $count = Product::query()
            ->whereIn('id', (array) $request->input('ids'))
            ->delete();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Validate product payload.
     */
    private function validateProductRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'min:2'],
            'description' => ['required', 'string', 'min:4'],
            'sackColor' => ['required', 'string', 'min:2'],
            'categoryId' => ['required', 'string'],
            'imageId' => ['nullable', 'string'],
            'nutritions' => ['required', 'array', 'min:1'],
            'nutritions.*.label' => ['required', 'string'],
            'nutritions.*.value' => ['required', 'string'],
        ], [
            'code.required' => 'Code is required',
            'name.min' => 'Name is required',
            'description.min' => 'Description is required',
            'sackColor.min' => 'Sack color is required',
            'categoryId.required' => 'Category is required',
            'nutritions.min' => 'At least one nutrition item is required',
            'nutritions.*.label.required' => 'Nutrition label is required',
            'nutritions.*.value.required' => 'Nutrition value is required',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProduct(Product $product): array
    {
        $product->loadMissing(['category', 'nutritions', 'image']);

        return [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
            'sackColor' => $product->sack_color,
            'categoryId' => $product->category_id,
            'imageId' => $product->image_id,
            'createdAt' => optional($product->created_at)->toISOString(),
            'updatedAt' => optional($product->updated_at)->toISOString(),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'icon' => $product->category->icon,
                'orderNumber' => $product->category->order_number,
            ] : null,
            'image' => $product->image ? [
                'id' => $product->image->id,
                'originalFileName' => $product->image->original_file_name,
                'systemPath' => $product->image->system_path,
                'thumbnailPath' => $product->image->thumbnail_path,
                'mimeType' => $product->image->mime_type,
                'size' => $product->image->size,
            ] : null,
            'nutritions' => $product->nutritions->map(fn ($nutrition) => [
                'id' => $nutrition->id,
                'label' => $nutrition->label,
                'value' => $nutrition->value,
            ])->values(),
        ];
    }

    /**
     * Normalize legacy sack color labels.
     */
    private function normalizeSackColor(string $value): string
    {
        return match (strtolower(trim($value))) {
            'orange', 'oranye' => 'Oranye',
            'pink', 'merah muda' => 'Merah Muda',
            default => trim($value),
        };
    }
}
