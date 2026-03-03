(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

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
        } catch (error) {
            console.error('Service worker registration failed', error);
        }
    };

    if (document.readyState === 'complete') {
        register();
        return;
    }

    window.addEventListener('load', register, { once: true });
})();

