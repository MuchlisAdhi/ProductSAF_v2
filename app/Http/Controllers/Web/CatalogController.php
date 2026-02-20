<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Home page with categories grid.
     */
    public function home()
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('order_number')
            ->orderBy('name')
            ->get();

        return view('catalog.home', [
            'categories' => $categories,
            'totalProducts' => $categories->sum('products_count'),
        ]);
    }

    /**
     * Product listing with filters.
     */
    public function products(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $categoryFilter = trim((string) $request->query('category', ''));
        $sackColorFilter = trim((string) $request->query('sackColor', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 12), [6, 12, 24, 48], 12);

        $builder = Product::query()->with(['image', 'category']);

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
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

        $totalCount = Product::query()->count();
        $filteredCount = (clone $builder)->count();
        $products = $builder->orderByDesc('created_at')->paginate($pageSize)->withQueryString();

        return view('catalog.products', [
            'title' => 'Semua Produk',
            'subtitle' => 'Gunakan filter dan pagination berbasis query parameter.',
            'basePath' => '/products',
            'backHref' => '/',
            'backLabel' => 'Back',
            'products' => $products,
            'query' => $query,
            'categoryFilter' => $categoryFilter,
            'sackColorFilter' => $sackColorFilter,
            'categories' => Category::query()->orderBy('order_number')->orderBy('name')->get(['id', 'name']),
            'sackColors' => Product::query()
                ->select('sack_color')
                ->distinct()
                ->orderBy('sack_color')
                ->pluck('sack_color'),
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'filteredCount' => $filteredCount,
            'categoryMeta' => null,
        ]);
    }

    /**
     * Product list by category.
     */
    public function byCategory(Request $request, string $id)
    {
        $category = Category::query()->findOrFail($id);

        $query = trim((string) $request->query('q', ''));
        $sackColorFilter = trim((string) $request->query('sackColor', ''));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 12), [6, 12, 24, 48], 12);

        $builder = Product::query()
            ->where('category_id', $category->id)
            ->with('image');

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('sack_color', 'like', "%{$query}%");
            });
        }

        if ($sackColorFilter !== '') {
            $builder->where('sack_color', $sackColorFilter);
        }

        $totalCount = Product::query()->where('category_id', $category->id)->count();
        $filteredCount = (clone $builder)->count();
        $products = $builder->orderByDesc('created_at')->paginate($pageSize)->withQueryString();

        return view('catalog.products', [
            'title' => $category->name,
            'subtitle' => 'Cari produk berdasarkan kode, nama, deskripsi, atau warna karung.',
            'basePath' => "/categories/{$category->id}",
            'backHref' => '/',
            'backLabel' => 'Back',
            'products' => $products,
            'query' => $query,
            'categoryFilter' => '',
            'sackColorFilter' => $sackColorFilter,
            'categories' => collect(),
            'sackColors' => Product::query()
                ->where('category_id', $category->id)
                ->select('sack_color')
                ->distinct()
                ->orderBy('sack_color')
                ->pluck('sack_color'),
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'filteredCount' => $filteredCount,
            'categoryMeta' => [
                'name' => $category->name,
                'icon' => $category->icon,
            ],
        ]);
    }

    /**
     * Product detail page.
     */
    public function show(Request $request, string $id)
    {
        $product = Product::query()
            ->with(['category', 'image', 'nutritions'])
            ->findOrFail($id);

        $returnTo = trim((string) $request->query('returnTo', ''));
        $safeReturnTo = str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//')
            ? $returnTo
            : '';

        return view('catalog.product-detail', [
            'product' => $product,
            'backHref' => $safeReturnTo ?: "/categories/{$product->category_id}",
            'backLabel' => $safeReturnTo !== '' ? 'Back to List' : 'Kembali',
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
}
