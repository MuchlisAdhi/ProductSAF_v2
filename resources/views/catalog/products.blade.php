@extends('layouts.app')

@section('content')
    <section class="catalog-page space-y-5">
        <div class="catalog-panel relative overflow-hidden rounded-3xl border border-white/70 bg-white/90 p-5 shadow-sm backdrop-blur sm:p-6">
            <div class="absolute -right-20 -top-16 h-52 w-52 rounded-full bg-emerald-200/35 blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 h-52 w-52 rounded-full bg-amber-200/30 blur-3xl"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-3">
                <div>
                    <a href="{{ $backHref }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                        <x-lucide-arrow-left class="h-4 w-4" />
                        Kembali
                    </a>
                    <h1 class="mt-3 text-2xl font-semibold text-slate-900 sm:text-3xl">{{ $title }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $subtitle }}</p>
                    @if($categoryMeta)
                        <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                            @include('partials.category-icon', [
                                'icon' => $categoryMeta['icon'],
                                'alt' => $categoryMeta['name'],
                                'imgClass' => 'h-4 w-4',
                                'textClass' => 'text-[10px] font-semibold',
                            ])
                            <span>{{ $categoryMeta['name'] }}</span>
                        </div>
                    @endif
                </div>
                <span id="products-count-badge" class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $filteredCount }} produk
                </span>
            </div>
        </div>

        <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form id="products-filter-form" method="GET" action="{{ $basePath }}" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-6">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Cari</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Cari kode, nama, deskripsi..."
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                    />
                </div>

                @if($categories->count() > 0)
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Kategori</label>
                        <select name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($categoryFilter === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Warna Karung</label>
                    <select name="sackColor" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Warna</option>
                        @foreach($sackColors as $color)
                            @php
                                $colorLabel = match (\Illuminate\Support\Str::lower((string) $color)) {
                                    'orange', 'oranye' => 'Oranye',
                                    'pink', 'merah muda' => 'Merah Muda',
                                    default => $color,
                                };
                            @endphp
                            <option value="{{ $color }}" @selected($sackColorFilter === $color)>{{ $colorLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Urutkan</label>
                    <select name="sort" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                        <option value="code_asc" @selected($sort === 'code_asc')>Kode A-Z</option>
                        <option value="code_desc" @selected($sort === 'code_desc')>Kode Z-A</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Nama Z-A</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Jumlah Baris</label>
                    <select name="pageSize" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        @foreach([6, 12, 24, 48] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-6 flex items-center justify-between gap-2">
                    <input type="hidden" name="page" value="1">
                    <a href="{{ $basePath }}" class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Setel ulang filter</a>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">Terapkan</button>
                </div>
            </form>
            <div id="products-offline-notice" class="hidden border-b border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-800 sm:px-5">
                Mode offline aktif. Pencarian dijalankan dari cache lokal.
            </div>

            <div class="p-4 sm:p-5">
                <div id="products-empty-state" class="{{ $products->count() === 0 ? '' : 'hidden ' }}rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center sm:px-6">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-white text-slate-500 ring-1 ring-slate-200">
                        <x-lucide-search-x class="h-6 w-6" />
                    </span>
                    <h3 class="mt-3 text-base font-semibold text-slate-900">Produk tidak ditemukan</h3>
                    <p class="mx-auto mt-1 max-w-lg text-sm text-slate-600">
                        Coba ubah filter pencarian, kategori, atau warna karung. Anda juga bisa setel ulang filter untuk melihat semua produk lagi.
                    </p>
                    <a href="{{ $basePath }}" class="mt-4 inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        Setel ulang filter
                    </a>
                </div>
                <div id="products-grid" class="{{ $products->count() === 0 ? 'hidden ' : '' }}grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $product)
                        <a
                            href="{{ route('products.show', $product->id) }}?returnTo={{ urlencode(request()->fullUrl()) }}"
                            class="js-product-card catalog-card catalog-product-card group relative rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md"
                            data-product-id="{{ $product->id }}"
                            data-product-code="{{ $product->code }}"
                            data-product-name="{{ $product->name }}"
                            data-product-description="{{ $product->description }}"
                            data-product-sack-color="{{ $product->sack_color }}"
                            data-product-category-id="{{ $product->category?->id }}"
                            data-product-category-name="{{ $product->category?->name }}"
                            data-product-image="{{ $product->image?->system_path ?? '' }}"
                            data-product-thumbnail="{{ $product->image?->thumbnail_path ?? '' }}"
                        >
                            <div class="catalog-product-body flex items-start gap-3">
                                <div class="relative h-24 w-20 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-28 sm:w-24">
                                    <div class="catalog-skeleton absolute inset-0"></div>
                                    <img
                                        src="{{ $product->image?->thumbnail_path ?? $product->image?->system_path ?? 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image' }}"
                                        alt="{{ $product->code }}"
                                        class="h-full w-full object-cover catalog-lazy-image transition-opacity duration-300"
                                        data-lazy-image
                                        onerror="this.onerror=null;this.src='https://placehold.co/120x180/e2e8f0/334155?text=No+Image';"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="low"
                                        width="120"
                                        height="180"
                                    >
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold tracking-wide text-emerald-700">{{ $product->code }}</p>
                                    <p class="mt-1 line-clamp-2 text-base font-semibold text-slate-900">{{ $product->name }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <x-sack-color-badge :color="$product->sack_color" variant="outline" class="px-2 py-0.5" />
                                        @if($product->category)
                                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800">{{ $product->category->name }}</span>
                                        @endif
                                    </div>
                                    <p class="catalog-product-desc-inline mt-2 text-xs leading-relaxed text-slate-600">{{ $product->description }}</p>
                                </div>
                            </div>
                            <div class="catalog-product-desc">
                                <p class="catalog-product-desc-text">{{ $product->description }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div id="products-pagination-wrap" class="border-t border-slate-200 bg-slate-50/70 px-4 py-3 sm:px-6">
                <div id="products-server-pagination">
                    {{ $products->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
                <div id="products-offline-pagination" class="hidden"></div>
            </div>
        </div>
    </section>

    @include('partials.whatsapp-floating-button')
@endsection

@push('scripts')
    <script>
        (() => {
            const skeletonFallbackMs = 3400;
            const form = document.getElementById('products-filter-form');
            const grid = document.getElementById('products-grid');
            const emptyState = document.getElementById('products-empty-state');
            const paginationWrap = document.getElementById('products-pagination-wrap');
            const serverPagination = document.getElementById('products-server-pagination');
            const offlinePagination = document.getElementById('products-offline-pagination');
            const offlineNotice = document.getElementById('products-offline-notice');
            const countBadge = document.getElementById('products-count-badge');
            const pageSizeSelect = form.querySelector('select[name="pageSize"]');
            const pageInput = form.querySelector('input[name="page"]');
            const currentFullUrl = @json(request()->fullUrl());
            const basePath = @json($basePath);
            const enforcedCategoryId = (() => {
                const match = String(basePath || '').match(/^\/categories\/([^/?#]+)/i);
                if (!match || !match[1]) {
                    return '';
                }

                try {
                    return decodeURIComponent(match[1]).trim();
                } catch (error) {
                    return String(match[1]).trim();
                }
            })();

            if (!form || !grid || !emptyState || !paginationWrap || !serverPagination || !offlinePagination || !offlineNotice || !countBadge || !pageSizeSelect || !pageInput) {
                return;
            }

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const normalize = (value) => String(value ?? '').trim().toLowerCase();
            const normalizedUrl = (value) => {
                if (typeof value !== 'string') return '';
                const trimmed = value.trim();
                if (trimmed === '') return '';
                try {
                    const parsed = new URL(trimmed, window.location.origin);
                    if (parsed.origin !== window.location.origin) return '';
                    return parsed.pathname + parsed.search;
                } catch (error) {
                    return '';
                }
            };

            const colorLabel = (value) => {
                const normalized = normalize(value);
                if (normalized === 'orange' || normalized === 'oranye') return 'Oranye';
                if (normalized === 'pink' || normalized === 'merah muda') return 'Merah Muda';
                return String(value ?? '').trim();
            };

            const colorClasses = (value) => {
                const normalized = normalize(value);
                if (normalized === 'orange' || normalized === 'oranye') return 'border-orange-300 bg-orange-50 text-orange-700';
                if (normalized === 'pink' || normalized === 'merah muda') return 'border-pink-300 bg-pink-50 text-pink-700';
                return 'border-slate-300 bg-slate-50 text-slate-700';
            };

            const productLink = (id) => {
                const path = `/products/${encodeURIComponent(id)}`;
                return `${path}?returnTo=${encodeURIComponent(currentFullUrl)}`;
            };

            const toProduct = (raw) => ({
                id: String(raw.id ?? ''),
                code: String(raw.code ?? ''),
                name: String(raw.name ?? ''),
                description: String(raw.description ?? ''),
                sack_color: String(raw.sack_color ?? ''),
                category_id: String(raw.category_id ?? raw.category?.id ?? ''),
                category_name: String(raw.category_name ?? raw.category?.name ?? ''),
                image: normalizedUrl(raw.image ?? raw.image_path ?? raw.image?.system_path ?? ''),
                thumbnail: normalizedUrl(raw.thumbnail ?? raw.thumbnail_path ?? raw.image?.thumbnail_path ?? ''),
            });

            const domProducts = () => Array.from(document.querySelectorAll('.js-product-card')).map((card) => toProduct({
                id: card.dataset.productId,
                code: card.dataset.productCode,
                name: card.dataset.productName,
                description: card.dataset.productDescription,
                sack_color: card.dataset.productSackColor,
                category_id: card.dataset.productCategoryId,
                category_name: card.dataset.productCategoryName,
                image: card.dataset.productImage,
                thumbnail: card.dataset.productThumbnail,
            }));

            const loadBootstrapProducts = async () => {
                try {
                    const response = await fetch('/pwa/bootstrap-data.json', { cache: 'no-store' });
                    if (!response.ok) {
                        throw new Error('Bootstrap fetch failed');
                    }

                    const payload = await response.json();
                    if (!Array.isArray(payload?.products)) {
                        return domProducts();
                    }

                    return payload.products.map((item) => toProduct(item)).filter((item) => item.id !== '');
                } catch (error) {
                    return domProducts();
                }
            };

            const sortProducts = (rows, sort) => {
                const cloned = rows.slice();
                if (sort === 'latest') {
                    return cloned.sort((a, b) => Number(b.id) - Number(a.id));
                }
                if (sort === 'code_desc') {
                    return cloned.sort((a, b) => b.code.localeCompare(a.code, 'id', { sensitivity: 'base' }));
                }
                if (sort === 'name_asc') {
                    return cloned.sort((a, b) => a.name.localeCompare(b.name, 'id', { sensitivity: 'base' }));
                }
                if (sort === 'name_desc') {
                    return cloned.sort((a, b) => b.name.localeCompare(a.name, 'id', { sensitivity: 'base' }));
                }

                return cloned.sort((a, b) => a.code.localeCompare(b.code, 'id', { sensitivity: 'base' }));
            };

            const renderProducts = (rows, totalCount = rows.length) => {
                if (rows.length === 0) {
                    grid.innerHTML = '';
                    grid.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                    countBadge.textContent = `${totalCount} produk`;
                    return;
                }

                grid.classList.remove('hidden');
                emptyState.classList.add('hidden');
                countBadge.textContent = `${totalCount} produk`;

                grid.innerHTML = rows.map((product) => {
                    const imageSrc = product.thumbnail || product.image || 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image';
                    const badgeColor = colorClasses(product.sack_color);
                    const sackLabel = colorLabel(product.sack_color) || '-';
                    const categoryBadge = product.category_name
                        ? `<span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800">${escapeHtml(product.category_name)}</span>`
                        : '';

                    return `
                        <a href="${productLink(product.id)}" class="js-product-card catalog-card catalog-product-card group relative rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md is-loaded">
                            <div class="catalog-product-body flex items-start gap-3">
                                <div class="relative h-24 w-20 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 sm:h-28 sm:w-24">
                                    <img src="${escapeHtml(imageSrc)}" alt="${escapeHtml(product.code)}" class="h-full w-full object-cover catalog-lazy-image is-loaded transition-opacity duration-300" onerror="this.onerror=null;this.src='https://placehold.co/120x180/e2e8f0/334155?text=No+Image';" loading="lazy" decoding="async" fetchpriority="low" width="120" height="180">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold tracking-wide text-emerald-700">${escapeHtml(product.code)}</p>
                                    <p class="mt-1 line-clamp-2 text-base font-semibold text-slate-900">${escapeHtml(product.name)}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold ${badgeColor}">${escapeHtml(sackLabel)}</span>
                                        ${categoryBadge}
                                    </div>
                                    <p class="catalog-product-desc-inline mt-2 text-xs leading-relaxed text-slate-600">${escapeHtml(product.description)}</p>
                                </div>
                            </div>
                            <div class="catalog-product-desc">
                                <p class="catalog-product-desc-text">${escapeHtml(product.description)}</p>
                            </div>
                        </a>
                    `;
                }).join('');
            };

            const filterProducts = (rows, formData) => {
                const query = normalize(formData.get('q'));
                const category = normalize(formData.get('category'));
                const sackColor = normalize(formData.get('sackColor'));
                const sort = normalize(formData.get('sort') || 'code_asc');
                const routeCategory = normalize(enforcedCategoryId);

                const filtered = rows.filter((product) => {
                    const inQuery = query === '' || normalize([
                        product.code,
                        product.name,
                        product.description,
                        product.sack_color,
                        product.category_name,
                    ].join(' ')).includes(query);

                    const inCategory = category === '' || normalize(product.category_id) === category;
                    const inRouteCategory = routeCategory === '' || normalize(product.category_id) === routeCategory;
                    const inSackColor = sackColor === '' || normalize(product.sack_color) === sackColor;
                    return inQuery && inCategory && inRouteCategory && inSackColor;
                });

                return sortProducts(filtered, sort);
            };

            const toPositiveInt = (value, fallback) => {
                const parsed = Number.parseInt(String(value ?? ''), 10);
                if (!Number.isFinite(parsed) || parsed <= 0) {
                    return fallback;
                }

                return parsed;
            };

            const buildOfflinePageButton = (label, page, disabled, active = false) => {
                const baseClass = 'inline-flex min-w-9 items-center justify-center rounded-lg border px-3 py-1.5 text-xs font-semibold transition';
                if (disabled) {
                    return `<span class="${baseClass} cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400">${label}</span>`;
                }

                if (active) {
                    return `<button type="button" data-offline-page="${page}" class="${baseClass} border-emerald-600 bg-emerald-600 text-white">${label}</button>`;
                }

                return `<button type="button" data-offline-page="${page}" class="${baseClass} border-slate-300 bg-white text-slate-700 hover:border-emerald-300 hover:text-emerald-700">${label}</button>`;
            };

            const renderOfflinePagination = ({ totalItems, pageSize, currentPage, totalPages }) => {
                if (totalItems === 0 || totalPages <= 1) {
                    offlinePagination.classList.add('hidden');
                    offlinePagination.innerHTML = '';
                    return;
                }

                const pageButtons = [];
                const maxButtons = 5;
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                startPage = Math.max(1, endPage - maxButtons + 1);

                for (let page = startPage; page <= endPage; page += 1) {
                    pageButtons.push(buildOfflinePageButton(String(page), page, false, page === currentPage));
                }

                const startItem = (currentPage - 1) * pageSize + 1;
                const endItem = Math.min(totalItems, currentPage * pageSize);

                offlinePagination.classList.remove('hidden');
                offlinePagination.innerHTML = `
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs font-semibold text-slate-600">
                            Menampilkan ${startItem}-${endItem} dari ${totalItems} produk (offline)
                        </p>
                        <div class="flex flex-wrap items-center gap-1.5">
                            ${buildOfflinePageButton('Prev', currentPage - 1, currentPage <= 1)}
                            ${pageButtons.join('')}
                            ${buildOfflinePageButton('Next', currentPage + 1, currentPage >= totalPages)}
                        </div>
                    </div>
                `;
            };

            const updateOfflineStateBanner = () => {
                if (navigator.onLine) {
                    offlineNotice.classList.add('hidden');
                    return;
                }

                offlineNotice.classList.remove('hidden');
            };

            const isMobileLikeDevice = () => window.matchMedia('(hover: none), (pointer: coarse)').matches;

            const collapseMobileExpandedCards = (exceptCard = null) => {
                document.querySelectorAll('.js-product-card.is-mobile-expanded').forEach((card) => {
                    if (card === exceptCard) {
                        return;
                    }
                    card.classList.remove('is-mobile-expanded');
                });
            };

            let hasBoundMobileOutsideClick = false;

            const initMobileCardExpansion = () => {
                if (!isMobileLikeDevice()) {
                    collapseMobileExpandedCards();
                    return;
                }

                document.querySelectorAll('.js-product-card').forEach((card) => {
                    if (card.dataset.mobileExpandBound === '1') {
                        return;
                    }

                    card.dataset.mobileExpandBound = '1';
                    card.addEventListener('click', (event) => {
                        const target = event.target;
                        if (!(target instanceof HTMLElement)) {
                            return;
                        }

                        if (target.closest('button, input, select, textarea, label, [data-offline-page]')) {
                            return;
                        }

                        const isExpanded = card.classList.contains('is-mobile-expanded');
                        if (!isExpanded) {
                            event.preventDefault();
                            collapseMobileExpandedCards(card);
                            card.classList.add('is-mobile-expanded');
                        }
                    });
                });

                if (!hasBoundMobileOutsideClick) {
                    document.addEventListener('click', (event) => {
                        if (!isMobileLikeDevice()) {
                            return;
                        }

                        const target = event.target;
                        if (!(target instanceof HTMLElement)) {
                            return;
                        }

                        if (target.closest('.js-product-card')) {
                            return;
                        }

                        collapseMobileExpandedCards();
                    }, true);

                    hasBoundMobileOutsideClick = true;
                }
            };

            const initCardSkeletons = () => {
                document.querySelectorAll('.js-product-card').forEach((card) => {
                    const image = card.querySelector('[data-lazy-image]');
                    if (!image) {
                        card.classList.add('is-loaded');
                        return;
                    }

                    const markLoaded = () => {
                        image.classList.add('is-loaded');
                        card.classList.add('is-loaded');
                    };

                    if (image.complete && image.naturalWidth > 0) {
                        requestAnimationFrame(markLoaded);
                    } else {
                        image.addEventListener('load', markLoaded, { once: true });
                        image.addEventListener('error', markLoaded, { once: true });
                    }

                    setTimeout(() => card.classList.add('is-loaded'), skeletonFallbackMs);
                });
            };

            initCardSkeletons();
            initMobileCardExpansion();
            updateOfflineStateBanner();

            const runOfflineSearch = async () => {
                const products = await loadBootstrapProducts();
                const formData = new FormData(form);
                const filtered = filterProducts(products, formData);
                const pageSize = toPositiveInt(formData.get('pageSize'), 12);
                const requestedPage = toPositiveInt(formData.get('page'), 1);
                const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
                const currentPage = Math.min(requestedPage, totalPages);
                const offset = (currentPage - 1) * pageSize;
                const pageRows = filtered.slice(offset, offset + pageSize);

                pageInput.value = String(currentPage);
                serverPagination.classList.add('hidden');
                renderProducts(pageRows, filtered.length);
                initMobileCardExpansion();
                renderOfflinePagination({
                    totalItems: filtered.length,
                    pageSize,
                    currentPage,
                    totalPages,
                });
            };

            form.addEventListener('submit', async (event) => {
                if (navigator.onLine) {
                    return;
                }

                event.preventDefault();
                pageInput.value = '1';
                await runOfflineSearch();
            });

            pageSizeSelect.addEventListener('change', async () => {
                if (navigator.onLine) {
                    return;
                }

                pageInput.value = '1';
                await runOfflineSearch();
            });

            paginationWrap.addEventListener('click', async (event) => {
                if (navigator.onLine) {
                    return;
                }

                const target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }

                const trigger = target.closest('[data-offline-page]');
                if (!trigger) {
                    return;
                }

                event.preventDefault();
                const nextPage = trigger.getAttribute('data-offline-page');
                pageInput.value = String(toPositiveInt(nextPage, 1));
                await runOfflineSearch();
            });

            window.addEventListener('offline', async () => {
                updateOfflineStateBanner();
                await runOfflineSearch();
            });

            window.addEventListener('online', () => {
                offlineNotice.classList.add('hidden');
                offlinePagination.classList.add('hidden');
                offlinePagination.innerHTML = '';
                serverPagination.classList.remove('hidden');
            });

            window.addEventListener('resize', () => {
                if (!isMobileLikeDevice()) {
                    collapseMobileExpandedCards();
                }
            });

            if (!navigator.onLine) {
                runOfflineSearch().catch(() => null);
            }
        })();
    </script>
@endpush
