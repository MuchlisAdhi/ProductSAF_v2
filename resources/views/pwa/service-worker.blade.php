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
const OFFLINE_SYNC_TAG = 'saf-admin-offline-sync';
const DYNAMIC_REFRESH_INTERVAL_MS = 5 * 60 * 1000;

let lastDynamicRefreshAt = 0;

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        await cacheStaticPrecache();
        await refreshDynamicResources({ force: true });
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
        await refreshDynamicResources({ force: false });
    })());
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
        const fallback = await caches.match(OFFLINE_URL);
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
            return caches.match(OFFLINE_URL) || new Response('Offline', { status: 503, statusText: 'Offline' });
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

        const staticCache = await caches.open(STATIC_CACHE);
        const fallback = await staticCache.match(OFFLINE_URL);
        if (fallback) {
            return fallback;
        }

        return new Response('Offline', { status: 503, statusText: 'Offline' });
    }
};

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
