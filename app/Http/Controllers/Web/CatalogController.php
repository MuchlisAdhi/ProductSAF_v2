<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    /**
     * Home page with categories grid.
     */
    public function home()
    {
        $categories = Cache::remember('catalog.home.categories', now()->addMinutes(10), function () {
            return Category::query()
                ->select(['id', 'name', 'icon', 'order_number'])
                ->withCount('products')
                ->orderBy('order_number')
                ->orderBy('name')
                ->get();
        });

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
        $sort = $this->resolveSort((string) $request->query('sort', 'code_asc'));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 12), [6, 12, 24, 48], 12);

        $builder = Product::query()
            ->select(['id', 'code', 'name', 'description', 'sack_color', 'category_id', 'image_id', 'created_at'])
            ->with([
                'image:id,system_path,thumbnail_path',
                'category:id,name',
            ]);

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

        $totalCount = Cache::remember('catalog.products.total_count', now()->addMinutes(10), fn () => Product::query()->count());
        $filteredCount = (clone $builder)->count();
        $this->applySort($builder, $sort);
        $products = $builder->paginate($pageSize)->withQueryString();

        return view('catalog.products', [
            'title' => 'Semua Produk',
            'subtitle' => 'Gunakan filter dan penomoran halaman berbasis parameter kueri.',
            'basePath' => '/products',
            'backHref' => '/',
            'backLabel' => 'Kembali',
            'products' => $products,
            'query' => $query,
            'categoryFilter' => $categoryFilter,
            'sackColorFilter' => $sackColorFilter,
            'sort' => $sort,
            'categories' => Cache::remember(
                'catalog.products.categories',
                now()->addMinutes(10),
                fn () => Category::query()->orderBy('order_number')->orderBy('name')->get(['id', 'name'])
            ),
            'sackColors' => Cache::remember(
                'catalog.products.sack_colors',
                now()->addMinutes(10),
                fn () => Product::query()->select('sack_color')->distinct()->orderBy('sack_color')->pluck('sack_color')
            ),
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
        $sort = $this->resolveSort((string) $request->query('sort', 'code_asc'));
        $pageSize = $this->resolvePageSize((int) $request->query('pageSize', 12), [6, 12, 24, 48], 12);

        $builder = Product::query()
            ->select(['id', 'code', 'name', 'description', 'sack_color', 'category_id', 'image_id', 'created_at'])
            ->where('category_id', $category->id)
            ->with('image:id,system_path,thumbnail_path');

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

        $totalCount = Cache::remember(
            'catalog.category.'.$category->id.'.total_count',
            now()->addMinutes(10),
            fn () => Product::query()->where('category_id', $category->id)->count()
        );
        $filteredCount = (clone $builder)->count();
        $this->applySort($builder, $sort);
        $products = $builder->paginate($pageSize)->withQueryString();

        return view('catalog.products', [
            'title' => $category->name,
            'subtitle' => 'Cari produk berdasarkan kode, nama, deskripsi, atau warna karung.',
            'basePath' => "/categories/{$category->id}",
            'backHref' => '/',
            'backLabel' => 'Kembali',
            'products' => $products,
            'query' => $query,
            'categoryFilter' => '',
            'sackColorFilter' => $sackColorFilter,
            'sort' => $sort,
            'categories' => collect(),
            'sackColors' => Cache::remember(
                'catalog.category.'.$category->id.'.sack_colors',
                now()->addMinutes(10),
                fn () => Product::query()
                    ->where('category_id', $category->id)
                    ->select('sack_color')
                    ->distinct()
                    ->orderBy('sack_color')
                    ->pluck('sack_color')
            ),
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
            ->select(['id', 'code', 'name', 'description', 'sack_color', 'category_id', 'image_id'])
            ->with([
                'category:id,name',
                'image:id,system_path,thumbnail_path',
                'nutritions:id,product_id,label,value',
            ])
            ->findOrFail($id);

        $relatedProducts = Product::query()
            ->select(['id', 'code', 'name', 'description', 'sack_color', 'category_id', 'image_id'])
            ->with([
                'image:id,system_path,thumbnail_path',
                'category:id,name',
            ])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->limit(6)
            ->get();

        $returnTo = trim((string) $request->query('returnTo', ''));
        $safeReturnTo = str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//')
            ? $returnTo
            : '';

        return view('catalog.product-detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
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

    /**
     * Resolve allowed sort key.
     */
    private function resolveSort(string $requested): string
    {
        $allowed = ['latest', 'code_asc', 'code_desc', 'name_asc', 'name_desc'];

        return in_array($requested, $allowed, true) ? $requested : 'code_asc';
    }

    /**
     * Apply selected sorting.
     */
    private function applySort($builder, string $sort): void
    {
        match ($sort) {
            'latest' => $builder->orderByDesc('created_at'),
            'code_asc' => $builder->orderBy('code'),
            'code_desc' => $builder->orderByDesc('code'),
            'name_asc' => $builder->orderBy('name'),
            'name_desc' => $builder->orderByDesc('name'),
            default => $builder->orderBy('code'),
        };
    }
}
