(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    if (window.__SAF_PWA_REGISTERING__) {
        return;
    }
    window.__SAF_PWA_REGISTERING__ = true;

    const DYNAMIC_REFRESH_SYNC_TAG = 'saf-pwa-dynamic-refresh';
    const PERIODIC_REFRESH_SYNC_TAG = 'saf-pwa-periodic-refresh';
    const PERIODIC_REFRESH_MIN_INTERVAL_MS = 24 * 60 * 60 * 1000;
    const PUBLIC_WARMUP_TIMEOUT_MS = 120 * 1000;
    const PUBLIC_WARMUP_MESSAGE_TYPE = 'WARMUP_PUBLIC_CACHE';
    const ANALYZER_UA_PATTERN = /(HeadlessChrome|Puppeteer|PWABuilder|Lighthouse)/i;
    let publicWarmupPromise = null;
    const userAgent = navigator.userAgent || '';
    const isLikelyAnalyzerRuntime = ANALYZER_UA_PATTERN.test(userAgent) || navigator.webdriver === true;

    const ensureStoragePersistence = async () => {
        try {
            if (!navigator.storage || typeof navigator.storage.persist !== 'function') {
                return false;
            }

            return await navigator.storage.persist();
        } catch (error) {
            return false;
        }
    };

    const askServiceWorkerWarmup = async (registration, options = {}) => {
        const timeoutMs = Number.isFinite(options.timeoutMs)
            ? Math.max(5000, Number(options.timeoutMs))
            : PUBLIC_WARMUP_TIMEOUT_MS;
        const force = Boolean(options.force);

        const worker = registration.active || registration.waiting || registration.installing;
        if (!worker) {
            return { ok: false, reason: 'service-worker-not-ready' };
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            let settled = false;
            const finish = (value) => {
                if (settled) {
                    return;
                }

                settled = true;
                resolve(value);
            };

            const timeoutHandle = window.setTimeout(() => {
                finish({ ok: false, reason: 'warmup-timeout' });
            }, timeoutMs);

            messageChannel.port1.onmessage = (event) => {
                window.clearTimeout(timeoutHandle);
                finish(event.data || { ok: false, reason: 'warmup-empty-response' });
            };

            try {
                worker.postMessage({
                    type: PUBLIC_WARMUP_MESSAGE_TYPE,
                    force,
                }, [messageChannel.port2]);
            } catch (error) {
                window.clearTimeout(timeoutHandle);
                finish({ ok: false, reason: 'warmup-postmessage-failed' });
            }
        });
    };

    const ensurePublicWarmup = async (options = {}) => {
        if (isLikelyAnalyzerRuntime) {
            return { ok: false, reason: 'analyzer-runtime' };
        }

        const force = Boolean(options.force);
        if (!force && publicWarmupPromise) {
            return publicWarmupPromise;
        }

        publicWarmupPromise = (async () => {
            try {
                await ensureStoragePersistence();

                const registration = await navigator.serviceWorker.ready;
                let result = await askServiceWorkerWarmup(registration, {
                    force,
                    timeoutMs: options.timeoutMs,
                });

                if (!force && (!result || result.ok !== true)) {
                    result = await askServiceWorkerWarmup(registration, {
                        force: true,
                        timeoutMs: options.timeoutMs,
                    });
                }

                return result;
            } catch (error) {
                return { ok: false, reason: 'warmup-exception' };
            }
        })();

        return publicWarmupPromise;
    };

    const registerPeriodicRefresh = async (registration) => {
        try {
            if (!('periodicSync' in registration)) {
                return;
            }

            if (!navigator.permissions || typeof navigator.permissions.query !== 'function') {
                return;
            }

            const permissionStatus = await navigator.permissions.query({
                name: 'periodic-background-sync',
            });

            if (permissionStatus.state !== 'granted') {
                return;
            }

            await registration.periodicSync.register(PERIODIC_REFRESH_SYNC_TAG, {
                minInterval: PERIODIC_REFRESH_MIN_INTERVAL_MS,
            });
        } catch (error) {
            // Ignore unsupported Periodic Background Sync environments.
        }
    };

    const requestDynamicCacheRefresh = async () => {
        try {
            const registration = await navigator.serviceWorker.ready;
            if (registration.active) {
                registration.active.postMessage({ type: 'REFRESH_DYNAMIC_CACHE' });
            }

            if ('sync' in registration) {
                await registration.sync.register(DYNAMIC_REFRESH_SYNC_TAG);
            }

            await registerPeriodicRefresh(registration);
        } catch (error) {
            // Ignore unsupported background sync / unavailable registration.
        }
    };

    const register = async () => {
        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js', {
                scope: '/',
            });

            if (registration.waiting) {
                registration.waiting.postMessage({ type: 'SKIP_WAITING' });
            }

            registration.addEventListener('updatefound', () => {
                const installing = registration.installing;
                if (!installing) {
                    return;
                }

                installing.addEventListener('statechange', () => {
                    if (installing.state === 'installed' && navigator.serviceWorker.controller) {
                        installing.postMessage({ type: 'SKIP_WAITING' });
                    }
                });
            });

            if (!isLikelyAnalyzerRuntime) {
                await requestDynamicCacheRefresh();
                window.setTimeout(() => {
                    ensurePublicWarmup({ force: false }).catch(() => null);
                }, 1200);
            }
        } catch (error) {
            console.error('Service worker registration failed', error);
        }
    };

    window.addEventListener('online', () => {
        if (isLikelyAnalyzerRuntime) {
            return;
        }

        requestDynamicCacheRefresh().catch(() => null);
        ensurePublicWarmup({ force: true, timeoutMs: PUBLIC_WARMUP_TIMEOUT_MS }).catch(() => null);
    });

    window.SafPwa = window.SafPwa || {};
    window.SafPwa.awaitInitialWarmup = async (options = {}) => ensurePublicWarmup(options);

    if (document.readyState !== 'loading') {
        register();
        return;
    }

    document.addEventListener('DOMContentLoaded', register, { once: true });
})();
