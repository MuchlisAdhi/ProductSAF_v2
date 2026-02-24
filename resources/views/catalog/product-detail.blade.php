@extends('layouts.app')

@section('content')
    <section class="catalog-page space-y-5">
        <div class="catalog-panel rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                <a href="{{ $backHref }}" class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                    <x-lucide-arrow-left class="h-4 w-4" />
                    {{ $backLabel }}
                </a>

                <div class="min-w-0 sm:ml-auto sm:pt-0.5 sm:text-left">
                    <p class="text-2xl font-semibold tracking-tight text-emerald-700 sm:text-3xl">{{ $product->code }}</p>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Detail produk & kandungan nutrisi.</p>
                </div>
            </div>
        </div>

        <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Pratinjau Produk</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-100 via-white to-slate-200 p-4 sm:p-6">
                    <div class="mb-3 flex flex-wrap items-center justify-end gap-3">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-white/90 p-1 ring-1 ring-slate-200">
                            <button type="button" id="product-zoom-out" class="rounded-md p-2 text-slate-700 transition hover:bg-slate-100" aria-label="Perbesar">
                                <x-lucide-zoom-out class="h-4 w-4" />
                            </button>
                            <button type="button" id="product-zoom-reset" class="rounded-md px-2 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">100%</button>
                            <button type="button" id="product-zoom-in" class="rounded-md p-2 text-slate-700 transition hover:bg-slate-100" aria-label="Perkecil">
                                <x-lucide-zoom-in class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div id="product-zoom-stage" class="relative mx-auto grid max-w-2xl place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white p-6 touch-none">
                        <div class="catalog-skeleton absolute inset-0"></div>
                        <img
                            id="zoomable-product-image"
                            src="{{ $product->image?->system_path ?? 'https://placehold.co/300x450/e2e8f0/334155?text=No+Image' }}"
                            alt="{{ $product->code }}"
                            class="catalog-lazy-image h-auto w-full max-w-[30rem] origin-center cursor-zoom-in object-contain transition-transform duration-200 ease-out will-change-transform"
                            loading="eager"
                            draggable="false"
                            data-lightbox-trigger
                        >
                    </div>
                    <p class="mt-2 text-center text-xs text-slate-500">Gunakan tombol perbesar atau gulir mouse di atas gambar.</p>
                </div>
            </div>
        </div>

        <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Deskripsi Produk</h2>
            </div>
            <div class="p-4 sm:p-5">
                @if(trim((string) $product->description) !== '')
                    <p class="mx-auto max-w-3xl whitespace-pre-line text-center text-sm leading-relaxed text-slate-700">{!! nl2br(e($product->description)) !!}</p>
                @else
                    <p class="text-center text-sm text-slate-500">Deskripsi produk belum tersedia.</p>
                @endif
            </div>
        </div>

        <div id="product-lightbox" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/95 p-3 sm:p-4">
            <button id="product-lightbox-close" type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-2 text-white backdrop-blur transition hover:bg-white/20" aria-label="Tutup pratinjau">
                <x-lucide-x class="h-5 w-5" />
            </button>
            <div id="product-lightbox-stage" class="relative flex h-full w-full select-none items-center justify-center overflow-hidden touch-none">
                <div class="catalog-skeleton absolute inset-0 m-auto max-h-[92vh] max-w-[92vw] rounded-xl"></div>
                <img
                    id="product-lightbox-image"
                    src="{{ $product->image?->system_path ?? 'https://placehold.co/900x1200/e2e8f0/334155?text=No+Image' }}"
                    alt="{{ $product->code }}"
                    class="catalog-lazy-image absolute left-1/2 top-1/2 max-h-[92vh] max-w-[92vw] origin-center object-contain will-change-transform"
                    draggable="false"
                >
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-5 z-10 flex justify-center">
                <div class="pointer-events-auto inline-flex items-center gap-1 rounded-full bg-black/55 p-1 text-white ring-1 ring-white/25 backdrop-blur">
                    <button type="button" id="product-lightbox-zoom-out" class="rounded-full p-2 transition hover:bg-white/15" aria-label="Zoom out">
                        <x-lucide-zoom-out class="h-4 w-4" />
                    </button>
                    <button type="button" id="product-lightbox-zoom-reset" class="rounded-full px-3 py-1 text-xs font-semibold transition hover:bg-white/15">100%</button>
                    <button type="button" id="product-lightbox-zoom-in" class="rounded-full p-2 transition hover:bg-white/15" aria-label="Zoom in">
                        <x-lucide-zoom-in class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                    <x-lucide-tag class="h-5 w-5" />
                </span>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Kode</p>
                <p class="mt-1 text-base font-semibold text-slate-900">{{ $product->code }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-center">
                <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-amber-100 text-amber-700">
                    <x-lucide-box class="h-5 w-5" />
                </span>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-amber-700">Warna Karung</p>
                <p class="mt-2">
                    <x-sack-color-badge :color="$product->sack_color" variant="outline" />
                </p>
            </div>
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-center">
                <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sky-700">
                    <x-lucide-layers class="h-5 w-5" />
                </span>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-sky-700">Kategori</p>
                <p class="mt-1 text-base font-semibold text-slate-900">{{ $product->category->name }}</p>
            </div>
        </div>

        <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                <h3 class="text-base font-semibold text-slate-900">Kandungan Nutrisi</h3>
                <p class="text-xs text-slate-600">Daftar parameter nutrisi dari produk terpilih.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-emerald-700 text-white">
                            <th class="px-4 py-3 text-left text-sm font-semibold sm:px-6">Parameter</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold sm:px-6">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->nutritions as $nutrition)
                            <tr class="border-t border-slate-200 odd:bg-white even:bg-emerald-50/40">
                                <td class="break-words px-4 py-3 text-sm text-slate-700 sm:px-6">{{ $nutrition->label }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 sm:px-6">{{ $nutrition->value }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-sm text-slate-600 sm:px-6">Tidak ada data nutrisi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($relatedProducts->isNotEmpty())
            <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Produk Terkait</h3>
                        <p class="text-xs text-slate-600">Produk lain dalam kategori yang sama.</p>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <button type="button" class="related-products-prev inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700" aria-label="Slide sebelumnya">
                            <x-lucide-chevron-left class="h-4 w-4" />
                        </button>
                        <button type="button" class="related-products-next inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700" aria-label="Slide berikutnya">
                            <x-lucide-chevron-right class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="swiper related-products-swiper overflow-visible px-1 py-2">
                        <div class="swiper-wrapper">
                        @foreach($relatedProducts as $relatedProduct)
                            <div class="swiper-slide">
                                <a href="{{ route('products.show', $relatedProduct->id) }}?returnTo={{ urlencode(request()->fullUrl()) }}" class="js-product-card related-carousel-card group relative block overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition">
                                    <div class="flex items-start gap-3">
                                        <div class="relative h-28 w-[5.5rem] shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                            <div class="catalog-skeleton absolute inset-0"></div>
                                            <img
                                                src="{{ $relatedProduct->image?->system_path ?? 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image' }}"
                                                alt="{{ $relatedProduct->code }}"
                                                class="h-full w-full object-cover catalog-lazy-image transition-opacity duration-300"
                                                data-lazy-image
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold tracking-wide text-emerald-700">{{ $relatedProduct->code }}</p>
                                            <p class="mt-1 line-clamp-2 text-base font-semibold text-slate-900">{{ $relatedProduct->name }}</p>
                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <x-sack-color-badge :color="$relatedProduct->sack_color" variant="outline" class="px-2 py-0.5" />
                                                @if($relatedProduct->category)
                                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800">{{ $relatedProduct->category->name }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-2 line-clamp-2 text-xs text-slate-600">{{ $relatedProduct->description }}</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                        </div>
                        <div class="swiper-pagination related-products-pagination mt-5 !relative !bottom-0"></div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const image = document.getElementById('zoomable-product-image');
            const stage = document.getElementById('product-zoom-stage');
            const zoomInButton = document.getElementById('product-zoom-in');
            const zoomOutButton = document.getElementById('product-zoom-out');
            const resetButton = document.getElementById('product-zoom-reset');
            const lightboxTrigger = document.querySelector('[data-lightbox-trigger]');
            const lightbox = document.getElementById('product-lightbox');
            const lightboxStage = document.getElementById('product-lightbox-stage');
            const lightboxImage = document.getElementById('product-lightbox-image');
            const lightboxCloseButton = document.getElementById('product-lightbox-close');
            const lightboxZoomInButton = document.getElementById('product-lightbox-zoom-in');
            const lightboxZoomOutButton = document.getElementById('product-lightbox-zoom-out');
            const lightboxZoomResetButton = document.getElementById('product-lightbox-zoom-reset');
            const relatedSwiperRoot = document.querySelector('.related-products-swiper');
            const relatedPrevButton = document.querySelector('.related-products-prev');
            const relatedNextButton = document.querySelector('.related-products-next');
            const relatedPagination = document.querySelector('.related-products-pagination');

            if (!image || !stage || !zoomInButton || !zoomOutButton || !resetButton || !lightboxTrigger || !lightbox || !lightboxStage || !lightboxImage || !lightboxCloseButton || !lightboxZoomInButton || !lightboxZoomOutButton || !lightboxZoomResetButton) return;

            const previewMinScale = 1;
            const previewMaxScale = 3.2;
            const previewStep = 0.18;
            const previewWheelSensitivity = 0.00095;
            const skeletonFallbackMs = 3400;
            let scale = 1;
            let offsetX = 0;
            let offsetY = 0;
            let isDragging = false;
            let dragStartX = 0;
            let dragStartY = 0;
            let dragStartOffsetX = 0;
            let dragStartOffsetY = 0;

            const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

            const maxOffsets = () => {
                const baseWidth = image.offsetWidth;
                const baseHeight = image.offsetHeight;
                const stageWidth = stage.clientWidth;
                const stageHeight = stage.clientHeight;

                return {
                    x: Math.max(0, (baseWidth * scale - stageWidth) / 2),
                    y: Math.max(0, (baseHeight * scale - stageHeight) / 2),
                };
            };

            const clampOffsets = () => {
                const limit = maxOffsets();
                offsetX = clamp(offsetX, -limit.x, limit.x);
                offsetY = clamp(offsetY, -limit.y, limit.y);
            };

            const applyScale = () => {
                clampOffsets();
                image.style.transition = isDragging
                    ? 'transform 0s'
                    : 'transform 240ms cubic-bezier(0.22, 1, 0.36, 1)';
                image.style.transform = `translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
                resetButton.textContent = `${Math.round(scale * 100)}%`;
                stage.classList.toggle('cursor-grab', scale > 1 && !isDragging);
                stage.classList.toggle('cursor-grabbing', isDragging);
            };

            const setScale = (nextScale) => {
                scale = clamp(nextScale, previewMinScale, previewMaxScale);
                if (scale === previewMinScale) {
                    offsetX = 0;
                    offsetY = 0;
                }
                applyScale();
            };

            zoomInButton.addEventListener('click', () => {
                setScale(scale + previewStep);
            });

            zoomOutButton.addEventListener('click', () => {
                setScale(scale - previewStep);
            });

            resetButton.addEventListener('click', () => {
                setScale(previewMinScale);
            });

            stage.addEventListener('wheel', (event) => {
                event.preventDefault();
                const nextScale = scale * (1 - event.deltaY * previewWheelSensitivity);
                setScale(nextScale);
            }, { passive: false });

            stage.addEventListener('pointerdown', (event) => {
                if (scale <= previewMinScale) return;

                isDragging = true;
                dragStartX = event.clientX;
                dragStartY = event.clientY;
                dragStartOffsetX = offsetX;
                dragStartOffsetY = offsetY;
                stage.setPointerCapture(event.pointerId);
                applyScale();
            });

            stage.addEventListener('pointermove', (event) => {
                if (!isDragging) return;

                offsetX = dragStartOffsetX + (event.clientX - dragStartX);
                offsetY = dragStartOffsetY + (event.clientY - dragStartY);
                applyScale();
            });

            const stopDrag = () => {
                if (!isDragging) return;

                isDragging = false;
                applyScale();
            };

            stage.addEventListener('pointerup', stopDrag);
            stage.addEventListener('pointercancel', stopDrag);
            stage.addEventListener('pointerleave', () => {
                if (!isDragging) return;
                stopDrag();
            });

            window.addEventListener('resize', applyScale);
            applyScale();

            const initImageSkeleton = (targetImage, container) => {
                if (!targetImage || !container) return;

                const markLoaded = () => {
                    targetImage.classList.add('is-loaded');
                    container.classList.add('is-loaded');
                };

                if (targetImage.complete && targetImage.naturalWidth > 0) {
                    requestAnimationFrame(markLoaded);
                } else {
                    targetImage.addEventListener('load', markLoaded, { once: true });
                    targetImage.addEventListener('error', markLoaded, { once: true });
                    setTimeout(markLoaded, skeletonFallbackMs);
                }
            };

            initImageSkeleton(image, stage);
            initImageSkeleton(lightboxImage, lightboxStage);

            const initCardSkeletons = () => {
                document.querySelectorAll('.js-product-card').forEach((card) => {
                    const cardImage = card.querySelector('[data-lazy-image]');
                    if (!cardImage) {
                        card.classList.add('is-loaded');
                        return;
                    }

                    const markLoaded = () => {
                        cardImage.classList.add('is-loaded');
                        card.classList.add('is-loaded');
                    };

                    if (cardImage.complete && cardImage.naturalWidth > 0) {
                        requestAnimationFrame(markLoaded);
                    } else {
                        cardImage.addEventListener('load', markLoaded, { once: true });
                        cardImage.addEventListener('error', markLoaded, { once: true });
                        setTimeout(() => card.classList.add('is-loaded'), skeletonFallbackMs);
                    }
                });
            };

            initCardSkeletons();

            const initRelatedProductsCarousel = () => {
                if (!relatedSwiperRoot || relatedSwiperRoot.dataset.swiperReady === '1') return true;
                if (typeof window.Swiper === 'undefined' || !window.SwiperModules) return false;

                const slidesCount = relatedSwiperRoot.querySelectorAll('.swiper-slide').length;
                if (slidesCount === 0) return true;

                const { Autoplay, Navigation, Pagination } = window.SwiperModules;

                const hasMultiSlides = slidesCount > 1;
                if (!hasMultiSlides) {
                    relatedPrevButton?.classList.add('hidden');
                    relatedNextButton?.classList.add('hidden');
                    relatedPagination?.classList.add('hidden');
                }

                new window.Swiper('.related-products-swiper', {
                    modules: [Navigation, Pagination, Autoplay],
                    grabCursor: true,
                    centeredSlides: false,
                    direction: 'horizontal',
                    loop: slidesCount > 3,
                    slidesPerView: 1.08,
                    spaceBetween: 12,
                    speed: 700,
                    allowTouchMove: hasMultiSlides,
                    watchSlidesProgress: true,
                    autoplay: slidesCount > 2 ? {
                        delay: 3000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    } : false,
                    navigation: {
                        nextEl: '.related-products-next',
                        prevEl: '.related-products-prev',
                    },
                    pagination: {
                        el: '.related-products-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 1.5,
                            spaceBetween: 16,
                        },
                        1024: {
                            slidesPerView: 2.15,
                            spaceBetween: 18,
                        },
                        1280: {
                            slidesPerView: 2.6,
                            spaceBetween: 22,
                        },
                    },
                });

                relatedSwiperRoot.dataset.swiperReady = '1';
                return true;
            };

            const bootRelatedCarousel = () => {
                if (initRelatedProductsCarousel()) return;

                let attempts = 0;
                const timer = setInterval(() => {
                    attempts += 1;
                    if (initRelatedProductsCarousel() || attempts >= 80) {
                        clearInterval(timer);
                    }
                }, 50);
            };

            bootRelatedCarousel();
            window.addEventListener('swiper:ready', bootRelatedCarousel, { once: true });

            let bodyOverflowBackup = '';
            const openLightbox = () => {
                lightbox.classList.remove('hidden');
                lightbox.classList.add('flex');
                bodyOverflowBackup = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                resetLightboxScale();
                requestAnimationFrame(resetLightboxScale);
            };

            const closeLightbox = () => {
                lightbox.classList.add('hidden');
                lightbox.classList.remove('flex');
                document.body.style.overflow = bodyOverflowBackup;
            };

            lightboxTrigger.addEventListener('click', openLightbox);
            lightboxCloseButton.addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', (event) => {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });
            lightboxStage.addEventListener('click', (event) => {
                if (event.target === lightboxStage) {
                    closeLightbox();
                }
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) {
                    closeLightbox();
                }
            });

            const lightboxMinScale = 1;
            const lightboxMaxScale = 5.2;
            const lightboxStep = 0.2;
            const lightboxWheelSensitivity = 0.00085;
            const pinchSensitivity = 0.82;
            const pinchDeadzone = 4;
            let lightboxScale = 1;
            let lightboxOffsetX = 0;
            let lightboxOffsetY = 0;
            let lightboxIsDragging = false;
            let lightboxDragStartX = 0;
            let lightboxDragStartY = 0;
            let lightboxDragStartOffsetX = 0;
            let lightboxDragStartOffsetY = 0;
            const pointers = new Map();
            let pinchDistanceStart = 0;
            let pinchScaleStart = 1;
            let pinchMidpointStart = null;
            let pinchOffsetStartX = 0;
            let pinchOffsetStartY = 0;

            const distanceBetween = (pointA, pointB) => {
                const x = pointB.x - pointA.x;
                const y = pointB.y - pointA.y;

                return Math.hypot(x, y);
            };

            const midpointBetween = (pointA, pointB) => ({
                x: (pointA.x + pointB.x) / 2,
                y: (pointA.y + pointB.y) / 2,
            });

            const getLightboxMaxOffsets = () => {
                const baseWidth = lightboxImage.offsetWidth;
                const baseHeight = lightboxImage.offsetHeight;
                const stageWidth = lightboxStage.clientWidth;
                const stageHeight = lightboxStage.clientHeight;

                return {
                    x: Math.max(0, (baseWidth * lightboxScale - stageWidth) / 2),
                    y: Math.max(0, (baseHeight * lightboxScale - stageHeight) / 2),
                };
            };

            const clampLightboxOffsets = () => {
                const limits = getLightboxMaxOffsets();
                lightboxOffsetX = clamp(lightboxOffsetX, -limits.x, limits.x);
                lightboxOffsetY = clamp(lightboxOffsetY, -limits.y, limits.y);
            };

            const applyLightboxScale = () => {
                clampLightboxOffsets();
                lightboxImage.style.transition = lightboxIsDragging
                    ? 'transform 0s'
                    : 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)';
                lightboxImage.style.transform = `translate3d(-50%, -50%, 0) translate3d(${lightboxOffsetX}px, ${lightboxOffsetY}px, 0) scale(${lightboxScale})`;
                lightboxZoomResetButton.textContent = `${Math.round(lightboxScale * 100)}%`;
                lightboxStage.classList.toggle('cursor-grab', lightboxScale > 1 && !lightboxIsDragging);
                lightboxStage.classList.toggle('cursor-grabbing', lightboxIsDragging);
            };

            const setLightboxScale = (nextScale) => {
                lightboxScale = clamp(nextScale, lightboxMinScale, lightboxMaxScale);
                if (lightboxScale === 1) {
                    lightboxOffsetX = 0;
                    lightboxOffsetY = 0;
                }
                applyLightboxScale();
            };

            const resetLightboxScale = () => {
                lightboxScale = 1;
                lightboxOffsetX = 0;
                lightboxOffsetY = 0;
                pointers.clear();
                lightboxIsDragging = false;
                applyLightboxScale();
            };

            lightboxZoomInButton.addEventListener('click', () => {
                setLightboxScale(lightboxScale + lightboxStep);
            });

            lightboxZoomOutButton.addEventListener('click', () => {
                setLightboxScale(lightboxScale - lightboxStep);
            });

            lightboxZoomResetButton.addEventListener('click', resetLightboxScale);
            lightboxImage.addEventListener('load', () => {
                if (lightbox.classList.contains('hidden')) return;
                requestAnimationFrame(resetLightboxScale);
            });

            lightboxStage.addEventListener('wheel', (event) => {
                event.preventDefault();
                const nextScale = lightboxScale * (1 - event.deltaY * lightboxWheelSensitivity);
                setLightboxScale(nextScale);
            }, { passive: false });

            lightboxStage.addEventListener('pointerdown', (event) => {
                pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
                lightboxStage.setPointerCapture(event.pointerId);

                if (pointers.size === 1 && lightboxScale > 1) {
                    lightboxIsDragging = true;
                    lightboxDragStartX = event.clientX;
                    lightboxDragStartY = event.clientY;
                    lightboxDragStartOffsetX = lightboxOffsetX;
                    lightboxDragStartOffsetY = lightboxOffsetY;
                }

                if (pointers.size === 2) {
                    const [firstPoint, secondPoint] = [...pointers.values()];
                    pinchDistanceStart = distanceBetween(firstPoint, secondPoint);
                    pinchScaleStart = lightboxScale;
                    pinchMidpointStart = midpointBetween(firstPoint, secondPoint);
                    pinchOffsetStartX = lightboxOffsetX;
                    pinchOffsetStartY = lightboxOffsetY;
                    lightboxIsDragging = false;
                }

                applyLightboxScale();
            });

            lightboxStage.addEventListener('pointermove', (event) => {
                if (!pointers.has(event.pointerId)) return;

                pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

                if (pointers.size === 2) {
                    const [firstPoint, secondPoint] = [...pointers.values()];
                    const distance = distanceBetween(firstPoint, secondPoint);
                    if (pinchDistanceStart > 0) {
                        const deltaDistance = Math.abs(distance - pinchDistanceStart);
                        if (deltaDistance >= pinchDeadzone) {
                            const rawRatio = distance / pinchDistanceStart;
                            const adjustedRatio = 1 + ((rawRatio - 1) * pinchSensitivity);
                            lightboxScale = clamp(pinchScaleStart * adjustedRatio, lightboxMinScale, lightboxMaxScale);
                        }
                    }

                    if (pinchMidpointStart) {
                        const midpoint = midpointBetween(firstPoint, secondPoint);
                        lightboxOffsetX = pinchOffsetStartX + (midpoint.x - pinchMidpointStart.x);
                        lightboxOffsetY = pinchOffsetStartY + (midpoint.y - pinchMidpointStart.y);
                    }
                    applyLightboxScale();
                    return;
                }

                if (lightboxIsDragging && pointers.size === 1) {
                    lightboxOffsetX = lightboxDragStartOffsetX + (event.clientX - lightboxDragStartX);
                    lightboxOffsetY = lightboxDragStartOffsetY + (event.clientY - lightboxDragStartY);
                    applyLightboxScale();
                }
            });

            const stopLightboxPointer = (event) => {
                pointers.delete(event.pointerId);
                if (pointers.size < 2) {
                    pinchDistanceStart = 0;
                    pinchMidpointStart = null;
                }
                if (pointers.size === 0) {
                    lightboxIsDragging = false;
                    applyLightboxScale();
                }
            };

            lightboxStage.addEventListener('pointerup', stopLightboxPointer);
            lightboxStage.addEventListener('pointercancel', stopLightboxPointer);
            lightboxStage.addEventListener('pointerleave', stopLightboxPointer);

            window.addEventListener('resize', applyLightboxScale);
        })();
    </script>
@endpush
