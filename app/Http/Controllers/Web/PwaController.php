<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Nutrition;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PwaController extends Controller
{
    /**
     * Serve web app manifest.
     */
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name', 'Sidoagung Farm Product Catalog'),
            'short_name' => 'SAF Product',
            'description' => 'Katalog produk Sidoagung Farm dengan dukungan offline.',
            'id' => '/?source=pwa',
            'start_url' => route('pwa.splash', absolute: false),
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'lang' => 'id-ID',
            'dir' => 'ltr',
            'categories' => ['business', 'productivity', 'shopping'],
            'background_color' => '#ffffff',
            'theme_color' => '#1b5e20',
            'icons' => [
                [
                    'src' => '/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => '/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
            'screenshots' => [
                [
                    'src' => '/images/pwa/screenshot-home.jpeg',
                    'sizes' => '5472x3648',
                    'type' => 'image/jpeg',
                    'form_factor' => 'wide',
                    'label' => 'Halaman beranda katalog Sidoagung Farm',
                ],
                [
                    'src' => '/images/pwa/screenshot-products.jpeg',
                    'sizes' => '5472x3648',
                    'type' => 'image/jpeg',
                    'form_factor' => 'wide',
                    'label' => 'Tampilan katalog produk Sidoagung Farm',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve dynamic service worker with current precache list.
     */
    public function serviceWorker(): Response
    {
        $precacheUrls = $this->buildPrecacheUrls();
        $cacheVersion = $this->buildCacheVersion($precacheUrls, $this->buildLightweightBootstrapVersion());

        return response()
            ->view('pwa.service-worker', [
                'cacheVersion' => $cacheVersion,
                'precacheUrls' => $precacheUrls,
                'offlineUrl' => route('pwa.offline', absolute: false),
                'offlineLoginUrl' => route('pwa.offline-login', absolute: false),
                'bootstrapDataUrl' => route('pwa.bootstrap-data', absolute: false),
            ], 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Service-Worker-Allowed', '/')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Serve full bootstrap data for PWA install/runtime refresh.
     */
    public function bootstrapData(): JsonResponse
    {
        $payload = $this->buildBootstrapPayload();

        return response()->json($payload, 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Offline fallback page.
     */
    public function offline(): View
    {
        return view('pwa.offline');
    }

    /**
     * Offline fallback page for admin login entry.
     */
    public function offlineLogin(): View
    {
        return view('pwa.offline-login');
    }

    /**
     * PWA launch splash screen page.
     */
    public function splash(): View
    {
        return view('pwa.splash');
    }

    /**
     * @return list<string>
     */
    private function buildPrecacheUrls(): array
    {
        $urls = array_merge($this->baseStaticPrecacheUrls(), [
            route('pwa.splash', absolute: false),
            route('pwa.bootstrap-data', absolute: false),
            '/api/categories',
            '/api/products',
        ]);

        $urls = array_merge($urls, $this->viteBuildAssets());

        return collect($urls)
            ->map(fn ($url) => $this->normalizePath($url))
            ->filter(fn (?string $url): bool => is_string($url) && $url !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Build one-shot bootstrap payload used by service worker.
     *
     * @return array<string, mixed>
     */
    private function buildBootstrapPayload(): array
    {
        $categories = Category::query()
            ->select(['id', 'name', 'icon', 'order_number', 'updated_at'])
            ->withCount('products')
            ->orderBy('order_number')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->select(['id', 'code', 'name', 'description', 'sack_color', 'category_id', 'image_id', 'updated_at'])
            ->with([
                'category:id,name,icon,order_number,updated_at',
                'image:id,original_file_name,system_path,thumbnail_path,mime_type,size,updated_at',
                'nutritions:id,product_id,label,value',
            ])
            ->orderBy('name')
            ->get();

        $assets = Asset::query()
            ->select(['id', 'original_file_name', 'system_path', 'thumbnail_path', 'mime_type', 'size', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get();

        $urls = [
            route('pwa.splash', absolute: false),
            '/',
            '/products',
            route('pwa.offline', absolute: false),
            route('pwa.offline-login', absolute: false),
            route('login', absolute: false),
            '/api/categories',
            '/api/products',
        ];

        foreach ($categories as $category) {
            $urls[] = '/categories/'.$category->id;
        }

        foreach ($products as $product) {
            $urls[] = '/products/'.$product->id;
            $urls[] = '/api/products/'.$product->id;
            if ($product->category_id) {
                $urls[] = '/categories/'.$product->category_id;
            }

            foreach ([
                $product->image?->system_path,
                $product->image?->thumbnail_path,
            ] as $path) {
                $normalized = $this->normalizePath($path);
                if ($normalized !== null) {
                    $urls[] = $normalized;
                }
            }
        }

        foreach ($assets as $asset) {
            foreach ([$asset->system_path, $asset->thumbnail_path] as $path) {
                $normalized = $this->normalizePath($path);
                if ($normalized !== null) {
                    $urls[] = $normalized;
                }
            }
        }

        $normalized = collect($urls)
            ->map(fn ($url) => $this->normalizePath($url))
            ->filter(fn (?string $url): bool => is_string($url) && $url !== '')
            ->unique()
            ->values()
            ->all();

        return [
            'version' => $this->buildBootstrapVersion($categories->count(), $products->count(), $assets->count()),
            'generatedAt' => now()->toIso8601String(),
            'urls' => $normalized,
            'categories' => $categories
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'order_number' => $category->order_number,
                    'products_count' => (int) ($category->products_count ?? 0),
                ])
                ->values()
                ->all(),
            'products' => $products
                ->map(function (Product $product): array {
                    return [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'sack_color' => $product->sack_color,
                        'category_id' => $product->category_id,
                        'image_id' => $product->image_id,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'icon' => $product->category->icon,
                            'order_number' => $product->category->order_number,
                        ] : null,
                        'image' => $product->image ? [
                            'id' => $product->image->id,
                            'original_file_name' => $product->image->original_file_name,
                            'system_path' => $product->image->system_path,
                            'thumbnail_path' => $product->image->thumbnail_path,
                            'mime_type' => $product->image->mime_type,
                            'size' => $product->image->size,
                        ] : null,
                        'nutritions' => $product->nutritions
                            ->map(fn ($nutrition): array => [
                                'id' => $nutrition->id,
                                'label' => $nutrition->label,
                                'value' => $nutrition->value,
                            ])
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all(),
            'assets' => $assets
                ->map(fn (Asset $asset): array => [
                    'id' => $asset->id,
                    'original_file_name' => $asset->original_file_name,
                    'system_path' => $asset->system_path,
                    'thumbnail_path' => $asset->thumbnail_path,
                    'mime_type' => $asset->mime_type,
                    'size' => $asset->size,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return list<string>
     */
    private function baseStaticPrecacheUrls(): array
    {
        return [
            route('pwa.splash', absolute: false),
            '/',
            '/products',
            route('pwa.offline', absolute: false),
            route('pwa.offline-login', absolute: false),
            route('login', absolute: false),
            '/favicon.ico',
            '/images/logo/saf-logo.png',
            '/images/logo/saf-logo-merah.ico',
            '/images/logo/logo-sidoagung-merah.png',
            '/icons/icon-192.png',
            '/icons/icon-512.png',
            '/fonts/pwa/Poppins-Light.ttf',
            '/fonts/pwa/Poppins-Regular.ttf',
            '/fonts/pwa/Poppins-Bold.ttf',
            '/fonts/pwa/Oswald-Var.ttf',
            '/images/default-avatar.svg',
            '/images/bg-office.jpeg',
            '/images/bg-silo1.jpeg',
            '/images/bg-silo2.jpeg',
            '/images/bg-silo3.jpeg',
            '/images/pwa/screenshot-home.jpeg',
            '/images/pwa/screenshot-products.jpeg',
        ];
    }

    /**
     * Build dynamic payload version from DB state.
     */
    private function buildBootstrapVersion(int $categoryCount, int $productCount, int $assetCount): string
    {
        $nutritionCount = Nutrition::query()->count();
        $latestNutritionId = (string) (Nutrition::query()->max('id') ?? '');

        $fingerprint = [
            $this->timestamp(Category::query()->max('updated_at')),
            $this->timestamp(Product::query()->max('updated_at')),
            $this->timestamp(Asset::query()->max('updated_at')),
            $categoryCount,
            $productCount,
            $assetCount,
            $nutritionCount,
            $latestNutritionId,
        ];

        return substr(sha1(implode('|', $fingerprint)), 0, 24);
    }

    /**
     * Build lightweight bootstrap version without loading full payload.
     */
    private function buildLightweightBootstrapVersion(): string
    {
        return $this->buildBootstrapVersion(
            Category::query()->count(),
            Product::query()->count(),
            Asset::query()->count(),
        );
    }

    /**
     * @return list<string>
     */
    private function viteBuildAssets(): array
    {
        $manifestPath = public_path('build/manifest.json');
        if (! is_file($manifestPath)) {
            return [];
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            return [];
        }

        $assets = [];
        foreach ($manifest as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $file = $entry['file'] ?? null;
            if (is_string($file) && $file !== '') {
                $assets[] = '/build/'.ltrim($file, '/');
            }

            $css = $entry['css'] ?? [];
            if (is_array($css)) {
                foreach ($css as $cssFile) {
                    if (is_string($cssFile) && $cssFile !== '') {
                        $assets[] = '/build/'.ltrim($cssFile, '/');
                    }
                }
            }
        }

        return array_values(array_unique($assets));
    }

    /**
     * Normalize URL/path to same-origin path.
     */
    private function normalizePath(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, 'data:')) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $parsed = parse_url($value);
            if (! is_array($parsed) || ! isset($parsed['path'])) {
                return null;
            }

            $path = (string) $parsed['path'];
            $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';

            return $path !== '' ? $path.$query : null;
        }

        return str_starts_with($value, '/') ? $value : '/'.$value;
    }

    /**
     * Create deterministic cache version from asset state.
     *
     * @param  list<string>  $precacheUrls
     */
    private function buildCacheVersion(array $precacheUrls, string $bootstrapVersion): string
    {
        $manifestPath = public_path('build/manifest.json');
        $fingerprint = [
            $this->timestamp(Product::query()->max('updated_at')),
            $this->timestamp(Category::query()->max('updated_at')),
            $this->timestamp(Asset::query()->max('updated_at')),
            is_file($manifestPath) ? (int) filemtime($manifestPath) : 0,
            count($precacheUrls),
            $bootstrapVersion,
        ];

        return 'saf-pwa-'.substr(sha1(implode('|', $fingerprint)), 0, 20);
    }

    /**
     * Normalize mixed datetime value to unix timestamp.
     */
    private function timestamp(mixed $value): int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_string($value)) {
            $parsed = strtotime($value);

            return $parsed !== false ? $parsed : 0;
        }

        return 0;
    }
}
