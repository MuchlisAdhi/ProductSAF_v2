<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
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
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#ffffff',
            'theme_color' => '#1b5e20',
            'icons' => [
                [
                    'src' => '/images/logo/saf-logo.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => '/images/logo/saf-logo.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
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
        $cacheVersion = $this->buildCacheVersion($precacheUrls);

        return response()
            ->view('pwa.service-worker', [
                'cacheVersion' => $cacheVersion,
                'precacheUrls' => $precacheUrls,
                'offlineUrl' => route('pwa.offline', absolute: false),
            ], 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Service-Worker-Allowed', '/')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Offline fallback page.
     */
    public function offline(): View
    {
        return view('pwa.offline');
    }

    /**
     * @return list<string>
     */
    private function buildPrecacheUrls(): array
    {
        $urls = [
            '/',
            '/products',
            route('pwa.offline', absolute: false),
            '/favicon.ico',
            '/images/logo/saf-logo.png',
            '/images/logo/saf-logo-merah.ico',
            '/images/logo/logo-sidoagung-merah.png',
            '/images/default-avatar.svg',
            '/images/bg-office.jpeg',
            '/images/bg-silo1.jpeg',
            '/images/bg-silo2.jpeg',
            '/images/bg-silo3.jpeg',
        ];

        $urls = array_merge($urls, $this->viteBuildAssets());

        foreach (Category::query()->pluck('id') as $categoryId) {
            $urls[] = '/categories/'.$categoryId;
        }

        Product::query()
            ->select(['id', 'category_id', 'image_id'])
            ->with(['image:id,system_path,thumbnail_path'])
            ->chunk(200, function ($products) use (&$urls): void {
                foreach ($products as $product) {
                    $urls[] = '/products/'.$product->id;
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
            });

        Asset::query()
            ->select(['system_path', 'thumbnail_path'])
            ->chunk(200, function ($assets) use (&$urls): void {
                foreach ($assets as $asset) {
                    foreach ([$asset->system_path, $asset->thumbnail_path] as $path) {
                        $normalized = $this->normalizePath($path);
                        if ($normalized !== null) {
                            $urls[] = $normalized;
                        }
                    }
                }
            });

        $normalized = collect($urls)
            ->map(fn ($url) => $this->normalizePath($url))
            ->filter(fn (?string $url): bool => is_string($url) && $url !== '')
            ->unique()
            ->values()
            ->all();

        return $normalized;
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
    private function buildCacheVersion(array $precacheUrls): string
    {
        $manifestPath = public_path('build/manifest.json');
        $fingerprint = [
            $this->timestamp(Product::query()->max('updated_at')),
            $this->timestamp(Category::query()->max('updated_at')),
            $this->timestamp(Asset::query()->max('updated_at')),
            is_file($manifestPath) ? (int) filemtime($manifestPath) : 0,
            count($precacheUrls),
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
