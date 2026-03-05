const CACHE_VERSION = @json($cacheVersion);
const CACHE_PREFIX = 'saf-pwa-';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;
const IMAGE_CACHE = `${CACHE_VERSION}-images`;
const DATA_CACHE = `${CACHE_VERSION}-data`;

const OFFLINE_URL = @json($offlineUrl);
const OFFLINE_LOGIN_URL = @json($offlineLoginUrl);
const PRECACHE_URLS = @json($precacheUrls);
const DYNAMIC_BOOTSTRAP_URL = @json($bootstrapDataUrl);
const DYNAMIC_VERSION_KEY = '/__pwa_bootstrap_version__';
const PUBLIC_WARMUP_KEY = '/__pwa_public_warmup_version__';
const DYNAMIC_REFRESH_SYNC_TAG = 'saf-pwa-dynamic-refresh';
const PERIODIC_REFRESH_SYNC_TAG = 'saf-pwa-periodic-refresh';
const OFFLINE_SYNC_TAG = 'saf-admin-offline-sync';
const DYNAMIC_REFRESH_INTERVAL_MS = 5 * 60 * 1000;
const INSTALL_WARMUP_TIMEOUT_MS = 90 * 1000;
const DEFAULT_PREFETCH_RETRIES = 2;
const DEFAULT_PREFETCH_CONCURRENCY = 4;
const SHELL_FALLBACK_URLS = ['/', '/products', '/splash-screen'];
const ANALYZER_UA_PATTERN = /(HeadlessChrome|Puppeteer|PWABuilder|Lighthouse)/i;
const EMERGENCY_OFFLINE_HTML = '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#0f172a;display:grid;place-items:center;min-height:100vh;padding:16px}main{max-width:520px;background:#fff;border:1px solid #d1d5db;border-radius:12px;padding:20px;box-shadow:0 10px 25px rgba(15,23,42,.08)}h1{margin:0 0 8px;font-size:1.2rem}p{margin:0;color:#475569;line-height:1.5}a{display:inline-block;margin-top:14px;background:#1b5e20;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700}</style></head><body><main><h1>Koneksi Internet Tidak Tersedia</h1><p>Halaman belum tersedia offline. Coba lagi saat internet aktif.</p><a href="/">Kembali ke Beranda</a></main></body></html>';

let lastDynamicRefreshAt = 0;
const workerUserAgent = (self.navigator && self.navigator.userAgent) ? self.navigator.userAgent : '';
const isLikelyAnalyzerRuntime = ANALYZER_UA_PATTERN.test(workerUserAgent) || Boolean(self.navigator && self.navigator.webdriver);
const wait = (ms) => new Promise((resolve) => {
    setTimeout(resolve, ms);
});
const withTimeout = async (promise, timeoutMs) => {
    let timeoutHandle;

    const timeoutPromise = new Promise((resolve) => {
        timeoutHandle = setTimeout(() => {
            resolve(null);
        }, timeoutMs);
    });

    try {
        return await Promise.race([promise, timeoutPromise]);
    } finally {
        clearTimeout(timeoutHandle);
    }
};
self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        if (isLikelyAnalyzerRuntime) {
            await cacheAnalyzerPrecache();
            await ensureOfflineFallbackCached();
            await ensureOfflineLoginFallbackCached();
        } else {
            await withTimeout(warmupPublicCache({ force: true, notify: false }), INSTALL_WARMUP_TIMEOUT_MS);
        }
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

    if (!isLikelyAnalyzerRuntime) {
        refreshDynamicResources({ force: false }).catch(() => null);
    }
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

    if (event.data.type === 'WARMUP_PUBLIC_CACHE') {
        event.waitUntil(handleWarmupMessage(event));
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
        scheduleDynamicRefresh().catch(() => null);
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
const isAdminPath = (pathname) => pathname === '/admin' || pathname.startsWith('/admin/');
const isLoginPath = (pathname) => pathname === '/login';

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

const cacheStaticPrecache = async () => {
    const urls = PRECACHE_URLS
        .map((url) => normalizeUrl(url))
        .filter((url) => typeof url === 'string' && url !== '');

    return cacheUrls(urls, {
        retries: DEFAULT_PREFETCH_RETRIES,
        concurrency: DEFAULT_PREFETCH_CONCURRENCY,
    });
};

const cacheAnalyzerPrecache = async () => {
    const lightweightUrls = [
        '/',
        '/products',
        '/splash-screen',
        OFFLINE_URL,
        OFFLINE_LOGIN_URL,
        '/login',
        '/login?next=%2Fadmin',
        '/api/categories',
        '/api/products',
    ]
        .map((url) => normalizeUrl(url))
        .filter((url) => typeof url === 'string' && url !== '');

    return cacheUrls(lightweightUrls, {
        retries: 0,
        concurrency: 2,
    });
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

const ensureOfflineLoginFallbackCached = async () => {
    const loginFallbackPath = normalizeUrl(OFFLINE_LOGIN_URL);
    if (!loginFallbackPath) {
        return;
    }

    const loginFallbackAbsolute = new URL(loginFallbackPath, self.location.origin).toString();
    const cache = await caches.open(STATIC_CACHE);

    try {
        const response = await fetch(new Request(loginFallbackAbsolute, {
            cache: 'reload',
        }));

        if (!response.ok) {
            return;
        }

        await cache.put(new Request(loginFallbackPath), response.clone());
        await cache.put(new Request(loginFallbackAbsolute), response.clone());
    } catch (error) {
        // Keep install resilient even if offline login prefetch fails once.
    }
};

const warmupPublicCache = async ({ force, notify }) => {
    const staticResult = await cacheStaticPrecache();
    await ensureOfflineFallbackCached();
    await ensureOfflineLoginFallbackCached();
    const dynamicResult = await refreshDynamicResources({ force, notify });
    const completed = Boolean(dynamicResult) && dynamicResult.failed === 0;

    if (completed) {
        await markPublicWarmup(dynamicResult?.version || null);
    }

    return {
        version: dynamicResult?.version || null,
        static: staticResult,
        dynamic: dynamicResult,
        completed,
    };
};

const handleWarmupMessage = async (event) => {
    const port = event.ports && event.ports[0] ? event.ports[0] : null;
    const respond = (payload) => {
        if (!port) {
            return;
        }

        try {
            port.postMessage(payload);
        } catch (error) {
            // Ignore port failures.
        }
    };

    try {
        const force = Boolean(event.data?.force);
        const result = await warmupPublicCache({ force, notify: true });
        respond({
            ok: Boolean(result.completed),
            result,
        });
    } catch (error) {
        respond({
            ok: false,
            error: error instanceof Error ? error.message : 'Warmup failed',
        });
    }
};

const markPublicWarmup = async (version) => {
    const dataCache = await caches.open(DATA_CACHE);
    const value = String(version || Date.now());
    await dataCache.put(PUBLIC_WARMUP_KEY, new Response(value, {
        headers: {
            'Content-Type': 'text/plain; charset=UTF-8',
        },
    }));
};

const scheduleDynamicRefresh = async () => {
    const now = Date.now();
    if (now - lastDynamicRefreshAt < DYNAMIC_REFRESH_INTERVAL_MS) {
        return;
    }

    lastDynamicRefreshAt = now;
    await refreshDynamicResources({ force: false });
};

const refreshDynamicResources = async ({ force, notify = true }) => {
    let response;
    try {
        response = await fetch(new Request(DYNAMIC_BOOTSTRAP_URL, {
            cache: 'no-store',
        }));
    } catch (error) {
        return {
            version: null,
            total: 0,
            success: 0,
            failed: 0,
            skipped: true,
        };
    }

    if (!response.ok) {
        return {
            version: null,
            total: 0,
            success: 0,
            failed: 0,
            skipped: true,
        };
    }

    const payloadClone = response.clone();
    let payload;
    try {
        payload = await response.json();
    } catch (error) {
        return {
            version: null,
            total: 0,
            success: 0,
            failed: 0,
            skipped: true,
        };
    }

    const dataCache = await caches.open(DATA_CACHE);
    await dataCache.put(DYNAMIC_BOOTSTRAP_URL, payloadClone);

    const nextVersion = String(payload?.version || '').trim();
    const previousVersionResponse = await dataCache.match(DYNAMIC_VERSION_KEY);
    const previousVersion = previousVersionResponse ? (await previousVersionResponse.text()) : '';

    if (!force && nextVersion !== '' && nextVersion === previousVersion) {
        return {
            version: nextVersion,
            total: 0,
            success: 0,
            failed: 0,
            skipped: true,
        };
    }

    const dynamicUrls = Array.isArray(payload?.urls) ? payload.urls : [];
    const cacheResult = await cacheUrls(dynamicUrls, {
        retries: DEFAULT_PREFETCH_RETRIES,
        concurrency: DEFAULT_PREFETCH_CONCURRENCY,
    });

    if (nextVersion !== '' && cacheResult.failed === 0) {
        await dataCache.put(DYNAMIC_VERSION_KEY, new Response(nextVersion, {
            headers: { 'Content-Type': 'text/plain; charset=UTF-8' },
        }));
    }

    if (notify) {
        await notifyClientsDynamicRefresh(nextVersion);
    }

    return {
        version: nextVersion || null,
        total: cacheResult.total,
        success: cacheResult.success,
        failed: cacheResult.failed,
        skipped: false,
    };
};

const cacheUrls = async (urls, options = {}) => {
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

    if (uniqueUrls.length === 0) {
        return {
            total: 0,
            success: 0,
            failed: 0,
        };
    }

    const retries = Number.isFinite(options.retries)
        ? Math.max(0, Number(options.retries))
        : DEFAULT_PREFETCH_RETRIES;
    const concurrency = Number.isFinite(options.concurrency)
        ? Math.max(1, Math.min(6, Number(options.concurrency)))
        : DEFAULT_PREFETCH_CONCURRENCY;

    const priorityWeight = (url) => {
        if (url === '/' || url === '/products' || url.startsWith('/login')) return 0;
        if (url.startsWith('/admin')) return 0;
        if (url.startsWith('/products?category=')) return 0;
        if (url.startsWith('/products/') || url.startsWith('/categories/') || url.startsWith('/api/products/')) return 1;
        if (url.startsWith('/api/')) return 2;
        if (isStaticAssetPath(url)) return 3;
        if (isImagePath(url)) return 4;
        return 5;
    };

    const queue = uniqueUrls
        .slice()
        .sort((a, b) => priorityWeight(a) - priorityWeight(b));
    const workers = [];
    let success = 0;
    let failed = 0;

    for (let index = 0; index < concurrency; index += 1) {
        workers.push((async () => {
            while (queue.length > 0) {
                const nextUrl = queue.shift();
                if (!nextUrl) {
                    continue;
                }

                const cached = await prefetchAndCache(nextUrl, { retries });
                if (cached) {
                    success += 1;
                } else {
                    failed += 1;
                }
            }
        })());
    }

    await Promise.all(workers);

    return {
        total: uniqueUrls.length,
        success,
        failed,
    };
};

const resolveCacheName = (pathname) => {
    if (
        pathname === OFFLINE_URL
        || pathname === '/offline'
        || pathname === OFFLINE_LOGIN_URL
        || pathname === '/offline-login'
    ) {
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

const prefetchAndCache = async (url, options = {}) => {
    const absoluteUrl = new URL(url, self.location.origin);
    const cacheName = resolveCacheName(absoluteUrl.pathname);
    const cache = await caches.open(cacheName);

    const retries = Number.isFinite(options.retries)
        ? Math.max(0, Number(options.retries))
        : DEFAULT_PREFETCH_RETRIES;
    const pathKey = absoluteUrl.pathname + absoluteUrl.search;

    for (let attempt = 0; attempt <= retries; attempt += 1) {
        try {
            const request = new Request(absoluteUrl.toString(), {
                cache: 'reload',
            });
            const response = await fetch(request);
            if (!response.ok) {
                if (attempt < retries) {
                    await wait(180 * (attempt + 1));
                    continue;
                }

                return false;
            }

            await cache.put(request, response.clone());
            await cache.put(new Request(pathKey), response.clone());
            return true;
        } catch (error) {
            if (attempt < retries) {
                await wait(180 * (attempt + 1));
                continue;
            }

            return false;
        }
    }

    return false;
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
        const cachedPage = await pageCache.match(request) || await pageCache.match(request, { ignoreSearch: true });
        if (cachedPage) {
            return cachedPage;
        }

        const requestUrl = new URL(request.url);
        const loginPageFallback = await getLoginPageFallbackResponse(requestUrl);

        if (isLoginPath(requestUrl.pathname) && loginPageFallback) {
            return loginPageFallback;
        }

        if (isAdminPath(requestUrl.pathname)) {
            const adminFallback = await getAdminLoginFallbackResponse();
            if (adminFallback) {
                return adminFallback;
            }

            if (loginPageFallback) {
                return loginPageFallback;
            }
        }

        const pathOnlyRequest = new Request(requestUrl.pathname);
        const cachedPathOnly = await pageCache.match(pathOnlyRequest) || await caches.match(requestUrl.pathname);
        if (cachedPathOnly) {
            return cachedPathOnly;
        }

        const categoryFallback = await getCategoryRouteFallbackResponse(requestUrl);
        if (categoryFallback) {
            return categoryFallback;
        }

        const shellFallback = await getShellFallbackResponse();
        if (shellFallback) {
            return shellFallback;
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

const getCategoryRouteFallbackResponse = async (requestUrl) => {
    const match = requestUrl.pathname.match(/^\/categories\/([^/?#]+)/i);
    if (!match || !match[1]) {
        return null;
    }

    const categoryId = String(match[1]).trim();
    if (categoryId === '') {
        return null;
    }

    const mappedProductsPath = `/products?category=${encodeURIComponent(categoryId)}`;
    const mappedProductsAbsolute = new URL(mappedProductsPath, self.location.origin).toString();
    const keys = [
        new Request(requestUrl.pathname),
        requestUrl.pathname,
        new Request(requestUrl.toString()),
        requestUrl.toString(),
        new Request(mappedProductsPath),
        mappedProductsPath,
        new Request(mappedProductsAbsolute),
        mappedProductsAbsolute,
    ];

    for (const key of keys) {
        const matchResponse = await caches.match(key, { ignoreSearch: false });
        if (matchResponse) {
            return matchResponse;
        }
    }

    return null;
};

const getLoginPageFallbackResponse = async (requestUrl) => {
    const loginPath = normalizeUrl('/login');
    if (!loginPath) {
        return null;
    }

    const loginPathWithAdminNext = normalizeUrl('/login?next=%2Fadmin');
    const loginAbsolute = new URL(loginPath, self.location.origin).toString();
    const requestPath = requestUrl.pathname + requestUrl.search;
    const requestAbsolute = requestUrl.toString();
    const keys = [
        new Request(requestPath),
        new Request(requestAbsolute),
        requestPath,
        requestAbsolute,
        loginPathWithAdminNext ? new Request(loginPathWithAdminNext) : null,
        loginPathWithAdminNext ? new Request(new URL(loginPathWithAdminNext, self.location.origin).toString()) : null,
        loginPathWithAdminNext || null,
        loginPath,
        new Request(loginPath),
        new Request(loginAbsolute),
        loginAbsolute,
    ].filter(Boolean);

    for (const key of keys) {
        const match = await caches.match(key);
        if (match) {
            return match;
        }
    }

    const ignoreSearchMatch = await caches.match(new Request(loginPath), { ignoreSearch: true });
    if (ignoreSearchMatch) {
        return ignoreSearchMatch;
    }

    return null;
};

const getAdminLoginFallbackResponse = async () => {
    const loginFallbackPath = normalizeUrl(OFFLINE_LOGIN_URL);
    if (!loginFallbackPath) {
        return null;
    }

    const loginFallbackAbsolute = new URL(loginFallbackPath, self.location.origin).toString();
    const keys = [
        new Request(loginFallbackPath),
        new Request(loginFallbackAbsolute),
        loginFallbackPath,
        loginFallbackAbsolute,
    ];

    for (const key of keys) {
        const match = await caches.match(key);
        if (match) {
            return match;
        }
    }

    return null;
};

const getShellFallbackResponse = async () => {
    for (const url of SHELL_FALLBACK_URLS) {
        const normalized = normalizeUrl(url);
        if (!normalized) {
            continue;
        }

        const absolute = new URL(normalized, self.location.origin).toString();
        const keys = [
            new Request(normalized),
            new Request(absolute),
            normalized,
            absolute,
        ];

        for (const key of keys) {
            const match = await caches.match(key, { ignoreSearch: true });
            if (match) {
                return match;
            }
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
