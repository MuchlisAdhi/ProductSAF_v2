(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const DYNAMIC_REFRESH_SYNC_TAG = 'saf-pwa-dynamic-refresh';

    const requestDynamicCacheRefresh = async () => {
        try {
            const registration = await navigator.serviceWorker.ready;
            if (registration.active) {
                registration.active.postMessage({ type: 'REFRESH_DYNAMIC_CACHE' });
            }

            if ('sync' in registration) {
                await registration.sync.register(DYNAMIC_REFRESH_SYNC_TAG);
            }
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

    if (document.readyState === 'complete') {
        register();
        return;
    }

    window.addEventListener('load', register, { once: true });
})();
