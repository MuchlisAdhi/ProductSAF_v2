const CACHE_VERSION = @json($cacheVersion);
const CACHE_PREFIX = 'saf-pwa-';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const IMAGE_CACHE = `${CACHE_VERSION}-images`;
const DATA_CACHE = `${CACHE_VERSION}-data`;

const OFFLINE_URL = @json($offlineUrl);
const PRECACHE_URLS = @json($precacheUrls);
const DYNAMIC_BOOTSTRAP_URL = @json($bootstrapDataUrl);
const DYNAMIC_VERSION_KEY = '/__pwa_bootstrap_version__';
const DYNAMIC_REFRESH_SYNC_TAG = 'saf-pwa-dynamic-refresh';
const PERIODIC_REFRESH_SYNC_TAG = 'saf-pwa-periodic-refresh';
const OFFLINE_SYNC_TAG = 'saf-admin-offline-sync';
const DYNAMIC_REFRESH_INTERVAL_MS = 5 * 60 * 1000;
const EMERGENCY_OFFLINE_HTML = '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#0f172a;display:grid;place-items:center;min-height:100vh;padding:16px}main{max-width:520px;background:#fff;border:1px solid #d1d5db;border-radius:12px;padding:20px;box-shadow:0 10px 25px rgba(15,23,42,.08)}h1{margin:0 0 8px;font-size:1.2rem}p{margin:0;color:#475569;line-height:1.5}a{display:inline-block;margin-top:14px;background:#1b5e20;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700}</style></head><body><main><h1>Koneksi Internet Tidak Tersedia</h1><p>Halaman belum tersedia offline. Coba lagi saat internet aktif.</p><a href="/">Kembali ke Beranda</a></main></body></html>';

let lastDynamicRefreshAt = 0;

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        await cacheStaticPrecache();
        await ensureOfflineFallbackCached();
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const cacheNames = await caches.keys();
        await Promise.all(
            cacheNames
                .filter((cacheName) => cacheName.startsWith(CACHE_PREFIX) && !cacheName.startsWith(CACHE_VERSION))
                .map((cacheName) => caches.delete(cacheName)),
        );

        await self.clients.claim();
    })());

    // Warm dynamic cache without blocking activation lifecycle.
    refreshDynamicResources({ force: false }).catch(() => null);
});

self.addEventListener('message', (event) => {
    if (!event.data || typeof event.data !== 'object') {
        return;
    }

    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data.type === 'REQUEST_SYNC_TRIGGER') {
        event.waitUntil(notifyClientsOfflineSync());
    }

    if (event.data.type === 'REFRESH_DYNAMIC_CACHE') {
        event.waitUntil(refreshDynamicResources({ force: false }));
    }
});

self.addEventListener('sync', (event) => {
    if (event.tag === OFFLINE_SYNC_TAG) {
        event.waitUntil(notifyClientsOfflineSync());
    }

    if (event.tag === DYNAMIC_REFRESH_SYNC_TAG) {
        event.waitUntil(refreshDynamicResources({ force: false }));
    }
});

self.addEventListener('periodicsync', (event) => {
    if (event.tag === PERIODIC_REFRESH_SYNC_TAG) {
        event.waitUntil(refreshDynamicResources({ force: false }));
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname === '/service-worker.js') {
        return;
    }

    if (request.mode === 'navigate') {
        event.waitUntil(scheduleDynamicRefresh());
        event.respondWith(handleNavigate(request));
        return;
    }

    if (url.pathname === DYNAMIC_BOOTSTRAP_URL || isPublicDataApi(url.pathname)) {
        event.respondWith(networkFirst(request, DATA_CACHE, { fallbackToOfflinePage: false }));
        return;
    }

    if (request.destination === 'image' || isImagePath(url.pathname)) {
        event.respondWith(cacheFirst(request, IMAGE_CACHE));
        return;
    }

    if (request.destination === 'script' || request.destination === 'style' || request.destination === 'font' || isStaticAssetPath(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
        return;
    }

    event.respondWith(networkFirst(request, PAGE_CACHE, { fallbackToOfflinePage: true }));
});

const isImagePath = (pathname) => /\.(png|jpg|jpeg|webp|gif|svg|ico)$/i.test(pathname);
const isStaticAssetPath = (pathname) => pathname.startsWith('/build/') || /\.(css|js|map|woff2?|ttf)$/i.test(pathname);
const isPublicDataApi = (pathname) => pathname === '/api/categories' || pathname === '/api/products' || pathname.startsWith('/api/products/');

const normalizeUrl = (value) => {
    if (typeof value !== 'string') {
        return null;
    }

    const trimmed = value.trim();
    if (trimmed === '') {
        return null;
    }

    try {
        const parsed = new URL(trimmed, self.location.origin);
        if (parsed.origin !== self.location.origin) {
            return null;
        }

        return parsed.pathname + parsed.search;
    } catch (error) {
        return null;
    }
};

const prefetchHeaders = {
    'X-PWA-Prefetch': '1',
    Accept: '*/*',
};

const cacheStaticPrecache = async () => {
    const urls = PRECACHE_URLS
        .map((url) => normalizeUrl(url))
        .filter((url) => typeof url === 'string' && url !== '');

    await cacheUrls(urls);
};

const ensureOfflineFallbackCached = async () => {
    const offlinePath = normalizeUrl(OFFLINE_URL);
    if (!offlinePath) {
        return;
    }

    const offlineAbsolute = new URL(offlinePath, self.location.origin).toString();
    const cache = await caches.open(STATIC_CACHE);

    try {
        const response = await fetch(new Request(offlineAbsolute, {
            cache: 'reload',
            headers: prefetchHeaders,
        }));

        if (!response.ok) {
            return;
        }

        await cache.put(new Request(offlinePath), response.clone());
        await cache.put(new Request(offlineAbsolute), response.clone());
    } catch (error) {
        // Keep install resilient even if fallback prefetch fails once.
    }
};

const scheduleDynamicRefresh = async () => {
    const now = Date.now();
    if (now - lastDynamicRefreshAt < DYNAMIC_REFRESH_INTERVAL_MS) {
        return;
    }

    lastDynamicRefreshAt = now;
    await refreshDynamicResources({ force: false });
};

const refreshDynamicResources = async ({ force }) => {
    let response;
    try {
        response = await fetch(new Request(DYNAMIC_BOOTSTRAP_URL, {
            cache: 'no-store',
            headers: prefetchHeaders,
        }));
    } catch (error) {
        return;
    }

    if (!response.ok) {
        return;
    }

    const payloadClone = response.clone();
    let payload;
    try {
        payload = await response.json();
    } catch (error) {
        return;
    }

    const dataCache = await caches.open(DATA_CACHE);
    await dataCache.put(DYNAMIC_BOOTSTRAP_URL, payloadClone);

    const nextVersion = String(payload?.version || '').trim();
    const previousVersionResponse = await dataCache.match(DYNAMIC_VERSION_KEY);
    const previousVersion = previousVersionResponse ? (await previousVersionResponse.text()) : '';

    if (!force && nextVersion !== '' && nextVersion === previousVersion) {
        return;
    }

    if (nextVersion !== '') {
        await dataCache.put(DYNAMIC_VERSION_KEY, new Response(nextVersion, {
            headers: { 'Content-Type': 'text/plain; charset=UTF-8' },
        }));
    }

    const dynamicUrls = Array.isArray(payload?.urls) ? payload.urls : [];
    await cacheUrls(dynamicUrls);
    await notifyClientsDynamicRefresh(nextVersion);
};

const cacheUrls = async (urls) => {
    const uniqueUrls = [];
    const seen = new Set();

    for (const rawUrl of urls) {
        const normalized = normalizeUrl(rawUrl);
        if (!normalized || seen.has(normalized)) {
            continue;
        }
        seen.add(normalized);
        uniqueUrls.push(normalized);
    }

    const queue = uniqueUrls.slice();
    const workers = [];
    const concurrency = 8;

    for (let index = 0; index < concurrency; index += 1) {
        workers.push((async () => {
            while (queue.length > 0) {
                const nextUrl = queue.shift();
                if (!nextUrl) {
                    continue;
                }
                await prefetchAndCache(nextUrl);
            }
        })());
    }

    await Promise.all(workers);
};

const resolveCacheName = (pathname) => {
    if (pathname === OFFLINE_URL || pathname === '/offline') {
        return STATIC_CACHE;
    }

    if (isImagePath(pathname)) {
        return IMAGE_CACHE;
    }

    if (isStaticAssetPath(pathname)) {
        return STATIC_CACHE;
    }

    if (pathname === DYNAMIC_BOOTSTRAP_URL || pathname.startsWith('/api/')) {
        return DATA_CACHE;
    }

    return PAGE_CACHE;
};

const prefetchAndCache = async (url) => {
    const absoluteUrl = new URL(url, self.location.origin);
    const cacheName = resolveCacheName(absoluteUrl.pathname);
    const cache = await caches.open(cacheName);

    try {
        const request = new Request(absoluteUrl.toString(), {
            cache: 'reload',
            headers: prefetchHeaders,
        });
        const response = await fetch(request);
        if (!response.ok) {
            return;
        }

        await cache.put(request, response.clone());
    } catch (error) {
        // Ignore each prefetch failure and keep progressing.
    }
};

const cacheFirst = async (request, cacheName) => {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const fallback = await getOfflineFallbackResponse();
        return fallback || new Response('Offline', { status: 503, statusText: 'Offline' });
    }
};

const networkFirst = async (request, cacheName, options = {}) => {
    const fallbackToOfflinePage = Boolean(options.fallbackToOfflinePage);
    const cache = await caches.open(cacheName);

    try {
        const response = await fetch(request);
        if (response.ok && response.type === 'basic') {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }

        if (fallbackToOfflinePage) {
            const fallback = await getOfflineFallbackResponse();
            return fallback || buildOfflineDocumentResponse();
        }

        return new Response(JSON.stringify({
            error: 'Offline',
        }), {
            status: 503,
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
        });
    }
};

const staleWhileRevalidate = async (request, cacheName) => {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request)
        .then(async (response) => {
            if (response.ok) {
                await cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => null);

    if (cached) {
        return cached;
    }

    const response = await fetchPromise;
    if (response) {
        return response;
    }

    return new Response('Offline', { status: 503, statusText: 'Offline' });
};

const handleNavigate = async (request) => {
    try {
        const response = await fetch(request);
        if (response.ok && response.type === 'basic') {
            const pageCache = await caches.open(PAGE_CACHE);
            await pageCache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const pageCache = await caches.open(PAGE_CACHE);
        const cachedPage = await pageCache.match(request);
        if (cachedPage) {
            return cachedPage;
        }

        const fallback = await getOfflineFallbackResponse();
        if (fallback) {
            return fallback;
        }

        return buildOfflineDocumentResponse();
    }
};

const getOfflineFallbackResponse = async () => {
    const offlinePath = normalizeUrl(OFFLINE_URL);
    if (!offlinePath) {
        return null;
    }

    const offlineAbsolute = new URL(offlinePath, self.location.origin).toString();
    const keys = [
        new Request(offlinePath),
        new Request(offlineAbsolute),
        offlinePath,
        offlineAbsolute,
    ];

    for (const key of keys) {
        const match = await caches.match(key);
        if (match) {
            return match;
        }
    }

    return null;
};

const buildOfflineDocumentResponse = () => new Response(EMERGENCY_OFFLINE_HTML, {
    status: 200,
    headers: {
        'Content-Type': 'text/html; charset=UTF-8',
    },
});

const notifyClientsOfflineSync = async () => {
    const clients = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true,
    });

    for (const client of clients) {
        client.postMessage({
            type: 'OFFLINE_SYNC_TRIGGER',
        });
    }
};

const notifyClientsDynamicRefresh = async (version) => {
    const clients = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true,
    });

    for (const client of clients) {
        client.postMessage({
            type: 'PWA_DYNAMIC_CACHE_REFRESHED',
            version: version || null,
        });
    }
};
