const CACHE_VERSION = @json($cacheVersion);
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const IMAGE_CACHE = `${CACHE_VERSION}-images`;
const OFFLINE_URL = @json($offlineUrl);
const PRECACHE_URLS = @json($precacheUrls);

const SYNC_TAG = 'saf-admin-offline-sync';

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);

        for (const url of PRECACHE_URLS) {
            try {
                const response = await fetch(new Request(url, { cache: 'reload' }));
                if (response.ok) {
                    await cache.put(url, response.clone());
                }
            } catch (error) {
                // Ignore individual failures so SW can still install.
            }
        }

        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const cacheNames = await caches.keys();
        await Promise.all(
            cacheNames
                .filter((cacheName) => cacheName.startsWith('saf-pwa-') && !cacheName.startsWith(CACHE_VERSION))
                .map((cacheName) => caches.delete(cacheName)),
        );

        await self.clients.claim();
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
});

self.addEventListener('sync', (event) => {
    if (event.tag === SYNC_TAG) {
        event.waitUntil(notifyClientsOfflineSync());
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
        event.respondWith(handleNavigate(request));
        return;
    }

    if (request.destination === 'image' || isImagePath(url.pathname)) {
        event.respondWith(cacheFirst(request, IMAGE_CACHE));
        return;
    }

    if (request.destination === 'script' || request.destination === 'style' || request.destination === 'font' || url.pathname.startsWith('/build/')) {
        event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
        return;
    }

    event.respondWith(networkFirst(request, PAGE_CACHE));
});

const isImagePath = (pathname) => /\.(png|jpg|jpeg|webp|gif|svg|ico)$/i.test(pathname);

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

const networkFirst = async (request, cacheName) => {
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

        return caches.match(OFFLINE_URL) || new Response('Offline', { status: 503, statusText: 'Offline' });
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

