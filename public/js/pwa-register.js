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

            await requestDynamicCacheRefresh();
        } catch (error) {
            console.error('Service worker registration failed', error);
        }
    };

    window.addEventListener('online', () => {
        requestDynamicCacheRefresh().catch(() => null);
    });

    if (document.readyState !== 'loading') {
        register();
        return;
    }

    document.addEventListener('DOMContentLoaded', register, { once: true });
})();
