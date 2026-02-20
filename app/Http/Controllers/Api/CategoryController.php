<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * List categories.
     */
    public function index()
    {
        $rows = Category::query()
            ->withCount('products')
            ->orderBy('order_number')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $rows->map(fn (Category $category) => $this->mapCategory($category)),
        ]);
    }

    /**
     * Create category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2'],
            'icon' => ['required', 'string', 'min:1'],
            'orderNumber' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.min' => 'Category name is required',
            'icon.min' => 'Icon name is required',
            'orderNumber.integer' => 'Order number must be a whole number',
            'orderNumber.min' => 'Order number must be 0 or greater',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $category = Category::create([
                'name' => (string) $request->string('name'),
                'icon' => (string) $request->string('icon'),
                'order_number' => $request->integer('orderNumber', 0),
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json(['error' => 'Category name already exists'], 409);
            }

            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        $category->loadCount('products');

        return response()->json([
            'data' => $this->mapCategory($category),
        ], 201);
    }

    /**
     * Update category by id.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::query()->find($id);
        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2'],
            'icon' => ['required', 'string', 'min:1'],
            'orderNumber' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.min' => 'Category name is required',
            'icon.min' => 'Icon name is required',
            'orderNumber.integer' => 'Order number must be a whole number',
            'orderNumber.min' => 'Order number must be 0 or greater',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $category->update([
                'name' => (string) $request->string('name'),
                'icon' => (string) $request->string('icon'),
                'order_number' => $request->integer('orderNumber', 0),
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json(['error' => 'Category name already exists'], 409);
            }

            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        $category->loadCount('products');

        return response()->json([
            'data' => $this->mapCategory($category),
        ]);
    }

    /**
     * Delete category.
     */
    public function destroy(string $id)
    {
        $category = Category::query()->find($id);
        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        try {
            $category->delete();
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'error' => 'Category is used by products and cannot be deleted',
                ], 409);
            }

            report($exception);

            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'icon' => $category->icon,
            'orderNumber' => $category->order_number,
            '_count' => [
                'products' => (int) ($category->products_count ?? 0),
            ],
        ];
    }
}
