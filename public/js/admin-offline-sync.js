(function () {
    const config = window.SAF_OFFLINE_SYNC_CONFIG || {};
    if (
        !config.categoryStoreEndpoint
        || !config.categoryUpdateEndpointTemplate
        || !config.categoryDeleteEndpointTemplate
        || !config.productStoreEndpoint
        || !config.productUpdateEndpointTemplate
        || !config.productDeleteEndpointTemplate
    ) {
        return;
    }

    const DB_NAME = 'saf-admin-offline-sync';
    const DB_VERSION = 1;
    const STORE_NAME = 'jobs';
    const SYNC_TAG = 'saf-admin-offline-sync';
    const STATUS_ID = 'saf-offline-sync-status';
    let syncing = false;

    const openDatabase = () => new Promise((resolve, reject) => {
        if (!('indexedDB' in window)) {
            reject(new Error('Browser does not support IndexedDB'));
            return;
        }

        const request = window.indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error || new Error('IndexedDB open failed'));
    });

    const requestToPromise = (request) => new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error || new Error('IndexedDB request failed'));
    });

    const waitTransaction = (transaction) => new Promise((resolve, reject) => {
        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error || new Error('IndexedDB transaction failed'));
        transaction.onabort = () => reject(transaction.error || new Error('IndexedDB transaction aborted'));
    });

    const addJob = async (job) => {
        const db = await openDatabase();
        try {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.add(job);
            const id = await requestToPromise(request);
            await waitTransaction(tx);

            return Number(id);
        } finally {
            db.close();
        }
    };

    const getJobs = async () => {
        const db = await openDatabase();
        try {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const jobs = await requestToPromise(store.getAll());
            await waitTransaction(tx);

            return (Array.isArray(jobs) ? jobs : [])
                .sort((left, right) => Number(left.createdAt || 0) - Number(right.createdAt || 0));
        } finally {
            db.close();
        }
    };

    const deleteJob = async (id) => {
        const db = await openDatabase();
        try {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.delete(id);
            await waitTransaction(tx);
        } finally {
            db.close();
        }
    };

    const countJobs = async () => {
        const db = await openDatabase();
        try {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const count = await requestToPromise(store.count());
            await waitTransaction(tx);

            return Number(count || 0);
        } finally {
            db.close();
        }
    };

    const notifier = (() => {
        if (typeof window.Notyf !== 'function') {
            return null;
        }

        return new window.Notyf({
            duration: 3800,
            position: { x: 'right', y: 'top' },
            dismissible: true,
            types: [
                { type: 'warning', background: '#f59e0b', icon: false },
                { type: 'info', background: '#0284c7', icon: false },
            ],
        });
    })();

    const notify = (type, message) => {
        if (!notifier) {
            if (type === 'error') {
                console.error(message);
            } else {
                console.log(message);
            }
            return;
        }

        if (type === 'success') {
            notifier.success(message);
            return;
        }

        if (type === 'error') {
            notifier.error(message);
            return;
        }

        notifier.open({ type, message });
    };

    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) {
            return '';
        }

        return String(meta.getAttribute('content') || '').trim();
    };

    const resolveEndpoint = (template, id) => {
        return String(template || '').replace('__ID__', encodeURIComponent(String(id || '').trim()));
    };

    const resolveEntityId = (form) => {
        const direct = String(form.dataset.offlineEntityId || '').trim();
        if (direct !== '') {
            return direct;
        }

        const action = String(form.getAttribute('action') || '').trim();
        const match = action.match(/\/admin\/(?:categories|products)\/([^/?#]+)/i);
        if (!match || !match[1]) {
            return '';
        }

        try {
            return decodeURIComponent(match[1]).trim();
        } catch (error) {
            return String(match[1]).trim();
        }
    };

    const ensureStatusElement = () => {
        let element = document.getElementById(STATUS_ID);
        if (element) {
            return element;
        }

        element = document.createElement('div');
        element.id = STATUS_ID;
        element.style.position = 'fixed';
        element.style.right = '16px';
        element.style.bottom = '16px';
        element.style.zIndex = '1080';
        element.style.borderRadius = '999px';
        element.style.padding = '8px 14px';
        element.style.fontSize = '12px';
        element.style.fontWeight = '700';
        element.style.boxShadow = '0 8px 20px rgba(15, 23, 42, 0.2)';
        element.style.transition = 'all .18s ease';
        element.style.maxWidth = 'calc(100vw - 32px)';
        element.style.whiteSpace = 'nowrap';
        element.style.overflow = 'hidden';
        element.style.textOverflow = 'ellipsis';
        document.body.appendChild(element);

        return element;
    };

    const renderStatus = async () => {
        const element = ensureStatusElement();
        const queued = await countJobs();

        if (!window.navigator.onLine) {
            element.textContent = queued > 0
                ? `Mode offline aktif: ${queued} antrean`
                : 'Mode offline aktif';
            element.style.background = '#b91c1c';
            element.style.color = '#ffffff';
            return;
        }

        if (queued > 0) {
            element.textContent = `Menunggu sinkronisasi: ${queued} antrean`;
            element.style.background = '#b45309';
            element.style.color = '#ffffff';
            return;
        }

        element.textContent = 'Online: sinkron';
        element.style.background = '#166534';
        element.style.color = '#ffffff';
    };

    const parseNutritions = (formData) => {
        const rows = new Map();

        for (const [key, value] of formData.entries()) {
            const match = key.match(/^nutritions\[(\d+)]\[(label|value)]$/);
            if (!match) {
                continue;
            }

            const rowIndex = Number(match[1]);
            if (!rows.has(rowIndex)) {
                rows.set(rowIndex, { label: '', value: '' });
            }
            rows.get(rowIndex)[match[2]] = String(value || '').trim();
        }

        return [...rows.keys()]
            .sort((a, b) => a - b)
            .map((index) => rows.get(index))
            .filter((row) => row && row.label !== '' && row.value !== '');
    };

    const parseProductPayload = (form) => {
        const formData = new FormData(form);
        const nutritions = parseNutritions(formData);
        const imageInput = form.querySelector('input[name="image"]');
        const imageFile = imageInput && imageInput.files && imageInput.files[0]
            ? imageInput.files[0]
            : null;

        const payload = {
            code: String(formData.get('code') || '').trim(),
            name: String(formData.get('name') || '').trim(),
            description: String(formData.get('description') || '').trim(),
            sack_color: String(formData.get('sack_color') || '').trim(),
            category_id: String(formData.get('category_id') || '').trim(),
            remove_image: String(formData.get('remove_image') || '0') === '1' ? '1' : '0',
            nutritions,
            image: imageFile,
        };

        if (
            payload.code === ''
            || payload.name === ''
            || payload.description === ''
            || payload.sack_color === ''
            || payload.category_id === ''
            || payload.nutritions.length === 0
        ) {
            throw new Error('Form produk belum lengkap');
        }

        return payload;
    };

    const queueCategoryCreateForm = async (form) => {
        const formData = new FormData(form);
        const payload = {
            name: String(formData.get('name') || '').trim(),
            icon: String(formData.get('icon') || '').trim(),
            order_number: String(formData.get('order_number') || '').trim(),
        };

        if (payload.name === '' || payload.icon === '' || payload.order_number === '') {
            throw new Error('Form kategori belum lengkap');
        }

        await addJob({
            type: 'category:create',
            createdAt: Date.now(),
            csrfToken: getCsrfToken(),
            payload,
        });
    };

    const queueCategoryUpdateForm = async (form) => {
        const id = resolveEntityId(form);
        if (id === '') {
            throw new Error('ID kategori tidak ditemukan');
        }

        const formData = new FormData(form);
        const payload = {
            id,
            name: String(formData.get('name') || '').trim(),
            icon: String(formData.get('icon') || '').trim(),
            order_number: String(formData.get('order_number') || '').trim(),
        };

        if (payload.name === '' || payload.icon === '' || payload.order_number === '') {
            throw new Error('Form kategori belum lengkap');
        }

        await addJob({
            type: 'category:update',
            createdAt: Date.now(),
            csrfToken: getCsrfToken(),
            payload,
        });
    };

    const queueCategoryDeleteForm = async (form) => {
        const id = resolveEntityId(form);
        if (id === '') {
            throw new Error('ID kategori tidak ditemukan');
        }

        await addJob({
            type: 'category:delete',
            createdAt: Date.now(),
            csrfToken: getCsrfToken(),
            payload: { id },
        });
    };

    const queueProductCreateForm = async (form) => {
        const payload = parseProductPayload(form);

        await addJob({
            type: 'product:create',
            createdAt: Date.now(),
            csrfToken: getCsrfToken(),
            payload,
        });
    };

    const queueProductUpdateForm = async (form) => {
        const id = resolveEntityId(form);
        if (id === '') {
            throw new Error('ID produk tidak ditemukan');
        }

        const payload = parseProductPayload(form);
        payload.id = id;

        await addJob({
            type: 'product:update',
            createdAt: Date.now(),
            csrfToken: getCsrfToken(),
            payload,
        });
    };

    const queueProductDeleteForm = async (form) => {
        const id = resolveEntityId(form);
        if (id === '') {
            throw new Error('ID produk tidak ditemukan');
        }

        await addJob({
            type: 'product:delete',
            createdAt: Date.now(),
            csrfToken: getCsrfToken(),
            payload: { id },
        });
    };

    const queueProductBulkDeleteForm = async (form) => {
        const formData = new FormData(form);
        const ids = formData.getAll('ids[]')
            .map((value) => String(value || '').trim())
            .filter((value) => value !== '');

        if (ids.length === 0) {
            throw new Error('Pilih setidaknya satu produk untuk dihapus.');
        }

        const createdAt = Date.now();
        const csrfToken = getCsrfToken();
        for (let index = 0; index < ids.length; index += 1) {
            await addJob({
                type: 'product:delete',
                createdAt: createdAt + index,
                csrfToken,
                payload: { id: ids[index] },
            });
        }

        return {
            queuedCount: ids.length,
        };
    };

    const buildFailure = (message, options = {}) => ({
        message,
        discard: Boolean(options.discard),
        stopSync: Boolean(options.stopSync),
    });

    const parseJsonSafe = async (response) => {
        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    };

    const appendProductPayload = (formData, payload, includeRemoveImage) => {
        formData.append('code', String(payload?.code || ''));
        formData.append('name', String(payload?.name || ''));
        formData.append('description', String(payload?.description || ''));
        formData.append('sack_color', String(payload?.sack_color || ''));
        formData.append('category_id', String(payload?.category_id || ''));

        const nutritions = Array.isArray(payload?.nutritions) ? payload.nutritions : [];
        nutritions.forEach((nutrition, index) => {
            formData.append(`nutritions[${index}][label]`, String(nutrition?.label || ''));
            formData.append(`nutritions[${index}][value]`, String(nutrition?.value || ''));
        });

        if (includeRemoveImage) {
            formData.append('remove_image', String(payload?.remove_image || '0') === '1' ? '1' : '0');
        }

        if (payload?.image instanceof File || payload?.image instanceof Blob) {
            const filename = payload?.image?.name || `offline-image-${Date.now()}.jpg`;
            formData.append('image', payload.image, filename);
        }
    };

    const syncOneJob = async (job) => {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        const csrfToken = getCsrfToken() || String(job.csrfToken || '').trim();
        if (csrfToken !== '') {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const formData = new FormData();
        let endpoint = '';

        if (job.type === 'category:create') {
            endpoint = config.categoryStoreEndpoint;
            formData.append('name', String(job.payload?.name || ''));
            formData.append('icon', String(job.payload?.icon || ''));
            formData.append('order_number', String(job.payload?.order_number || '0'));
        } else if (job.type === 'category:update') {
            const id = String(job.payload?.id || '').trim();
            endpoint = resolveEndpoint(config.categoryUpdateEndpointTemplate, id);
            formData.append('name', String(job.payload?.name || ''));
            formData.append('icon', String(job.payload?.icon || ''));
            formData.append('order_number', String(job.payload?.order_number || '0'));
        } else if (job.type === 'category:delete') {
            const id = String(job.payload?.id || '').trim();
            endpoint = resolveEndpoint(config.categoryDeleteEndpointTemplate, id);
        } else if (job.type === 'product:create') {
            endpoint = config.productStoreEndpoint;
            appendProductPayload(formData, job.payload, false);
        } else if (job.type === 'product:update') {
            const id = String(job.payload?.id || '').trim();
            endpoint = resolveEndpoint(config.productUpdateEndpointTemplate, id);
            appendProductPayload(formData, job.payload, true);
        } else if (job.type === 'product:delete') {
            const id = String(job.payload?.id || '').trim();
            endpoint = resolveEndpoint(config.productDeleteEndpointTemplate, id);
        } else {
            throw buildFailure('Job offline tidak dikenali', { discard: true });
        }

        if (endpoint === '' || endpoint.includes('__ID__')) {
            throw buildFailure('Endpoint sinkronisasi tidak valid', { discard: true });
        }

        let response;
        try {
            response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers,
                body: formData,
            });
        } catch (error) {
            throw buildFailure('Koneksi masih tidak stabil. Sinkronisasi ditunda.');
        }

        const payload = await parseJsonSafe(response);
        if (response.ok && (!payload || payload.success !== false)) {
            return;
        }

        if (response.status === 401 || response.status === 403 || response.status === 419) {
            throw buildFailure('Sesi admin habis. Silakan login ulang.', { stopSync: true });
        }

        if (response.status === 404 || response.status === 409 || response.status === 422) {
            const errorMessage = payload && payload.error
                ? String(payload.error)
                : 'Data antrean ditolak server dan dihapus dari lokal.';
            throw buildFailure(errorMessage, { discard: true });
        }

        const errorMessage = payload && payload.error
            ? String(payload.error)
            : 'Sinkronisasi gagal sementara.';
        throw buildFailure(errorMessage);
    };

    const requestBackgroundSync = async () => {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            if ('sync' in registration) {
                await registration.sync.register(SYNC_TAG);
            } else if (registration.active) {
                registration.active.postMessage({ type: 'REQUEST_SYNC_TRIGGER' });
            }
        } catch (error) {
            // Ignore unsupported background sync.
        }
    };

    const flushQueue = async () => {
        if (syncing || !window.navigator.onLine) {
            return;
        }

        syncing = true;

        try {
            const jobs = await getJobs();
            if (jobs.length === 0) {
                await renderStatus();
                return;
            }

            let syncedCount = 0;

            for (const job of jobs) {
                try {
                    await syncOneJob(job);
                    await deleteJob(job.id);
                    syncedCount += 1;
                } catch (failure) {
                    if (failure && failure.discard) {
                        await deleteJob(job.id);
                        notify('warning', `Antrean dihapus: ${failure.message}`);
                        continue;
                    }

                    if (failure && failure.stopSync) {
                        notify('warning', failure.message);
                    } else if (failure && failure.message) {
                        notify('info', failure.message);
                    }
                    break;
                }
            }

            if (syncedCount > 0) {
                notify('success', `${syncedCount} antrean offline berhasil disinkronkan.`);
            }

            const remaining = await countJobs();
            if (remaining > 0) {
                await requestBackgroundSync();
            }
        } finally {
            syncing = false;
            await renderStatus();
        }
    };

    const attachOfflineFormHandler = (selector, queueHandler, successMessage) => {
        document.querySelectorAll(selector).forEach((form) => {
            if (!(form instanceof HTMLFormElement) || form.dataset.offlineSyncBound === '1') {
                return;
            }

            form.dataset.offlineSyncBound = '1';

            form.addEventListener('submit', async (event) => {
                if (window.navigator.onLine) {
                    return;
                }

                event.preventDefault();

                if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                    return;
                }

                try {
                    const result = await queueHandler(form);
                    const message = typeof successMessage === 'function'
                        ? successMessage(result)
                        : successMessage;
                    notify('success', message);
                    await renderStatus();
                    await requestBackgroundSync();
                } catch (error) {
                    const message = error instanceof Error ? error.message : 'Gagal menyimpan antrean offline';
                    notify('error', message);
                }
            });
        });
    };

    const init = async () => {
        attachOfflineFormHandler(
            'form[data-offline-queue-form="category-create"]',
            queueCategoryCreateForm,
            'Tidak ada sinyal: kategori baru disimpan di lokal dan akan disinkronkan saat online.',
        );

        attachOfflineFormHandler(
            'form[data-offline-queue-form="category-update"]',
            queueCategoryUpdateForm,
            'Tidak ada sinyal: perubahan kategori disimpan di lokal dan akan disinkronkan saat online.',
        );

        attachOfflineFormHandler(
            'form[data-offline-queue-form="category-delete"]',
            queueCategoryDeleteForm,
            'Tidak ada sinyal: penghapusan kategori dimasukkan ke antrean lokal.',
        );

        attachOfflineFormHandler(
            'form[data-offline-queue-form="product-create"]',
            queueProductCreateForm,
            'Tidak ada sinyal: produk baru disimpan di lokal dan akan disinkronkan saat online.',
        );

        attachOfflineFormHandler(
            'form[data-offline-queue-form="product-update"]',
            queueProductUpdateForm,
            'Tidak ada sinyal: perubahan produk disimpan di lokal dan akan disinkronkan saat online.',
        );

        attachOfflineFormHandler(
            'form[data-offline-queue-form="product-delete"]',
            queueProductDeleteForm,
            'Tidak ada sinyal: penghapusan produk dimasukkan ke antrean lokal.',
        );

        attachOfflineFormHandler(
            'form[data-offline-queue-form="product-bulk-delete"]',
            queueProductBulkDeleteForm,
            (result) => {
                const count = Number(result?.queuedCount || 0);
                if (count > 0) {
                    return `Tidak ada sinyal: ${count} produk dimasukkan ke antrean hapus offline.`;
                }

                return 'Tidak ada sinyal: antrean hapus produk disimpan di lokal.';
            },
        );

        await renderStatus();
        if (window.navigator.onLine) {
            await flushQueue();
        }
    };

    window.addEventListener('online', async () => {
        notify('info', 'Koneksi kembali aktif. Memulai sinkronisasi antrean offline.');
        await flushQueue();
    });

    window.addEventListener('offline', async () => {
        await renderStatus();
    });

    window.setInterval(async () => {
        if (window.navigator.onLine) {
            await flushQueue();
        } else {
            await renderStatus();
        }
    }, 30000);

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', async (event) => {
            if (event.data && event.data.type === 'OFFLINE_SYNC_TRIGGER') {
                await flushQueue();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init().catch((error) => {
                console.error(error);
            });
        }, { once: true });
    } else {
        init().catch((error) => {
            console.error(error);
        });
    }
})();

