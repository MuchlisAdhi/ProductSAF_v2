@extends('layouts.app')

@push('head')
    <style>
        .product-share-menu {
            position: relative;
            display: inline-block;
            flex: 0 1 auto;
        }

        .product-preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            flex-wrap: nowrap;
        }

        .product-zoom-controls {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .product-share-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border: 1px solid rgba(15, 118, 110, 0.24);
            border-radius: 9999px;
            padding: 0.62rem 1.1rem;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0f766e, #059669);
            box-shadow: 0 10px 22px rgba(5, 150, 105, 0.26);
            cursor: pointer;
            list-style: none;
            user-select: none;
            white-space: nowrap;
            transition: transform 0.24s ease, box-shadow 0.24s ease, filter 0.24s ease;
            animation: product-share-pulse 3s infinite;
        }

        .product-share-toggle::-webkit-details-marker {
            display: none;
        }

        .product-share-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(5, 150, 105, 0.34);
            filter: brightness(1.03);
        }

        .product-share-menu[open] .product-share-toggle {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(5, 150, 105, 0.34);
        }

        .product-share-tooltip {
            position: absolute;
            left: 50%;
            top: calc(100% + 0.65rem);
            transform: translateX(-50%) scale(0.95);
            min-width: 12rem;
            border-radius: 1rem;
            border: 1px solid #cbd5e1;
            padding: 0.85rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.2);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.24s ease, transform 0.24s ease, visibility 0.24s ease;
            z-index: 12;
        }

        .product-share-menu[open] .product-share-tooltip {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateX(-50%) scale(1);
        }

        .product-share-tooltip::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -0.5rem;
            transform: translateX(-50%) rotate(45deg);
            width: 1rem;
            height: 1rem;
            border-top: 1px solid #cbd5e1;
            border-left: 1px solid #cbd5e1;
            background: rgba(255, 255, 255, 0.98);
        }

        .product-share-icons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .product-share-note {
            margin-top: 0.5rem;
            font-size: 0.68rem;
            text-align: center;
            color: #64748b;
            line-height: 1.25;
        }

        .product-share-icon {
            width: 2.7rem;
            height: 2.7rem;
            border: 0;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            background: #f1f5f9;
            box-shadow: 0 7px 14px rgba(15, 23, 42, 0.14);
            transition: transform 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .product-share-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(15, 23, 42, 0.2);
            color: #ffffff;
        }

        .product-share-icon--whatsapp:hover {
            background: linear-gradient(135deg, #25d366, #17b455);
        }

        .product-share-icon--instagram:hover {
            background: linear-gradient(135deg, #f97316, #ec4899);
        }

        .product-share-icon--facebook:hover {
            background: linear-gradient(135deg, #1877f2, #165ed0);
        }

        .product-share-icon svg {
            width: 1.2rem;
            height: 1.2rem;
            fill: currentColor;
        }

        @media (max-width: 640px) {
            .product-preview-toolbar {
                gap: 0.45rem;
            }

            .product-share-toggle {
                justify-content: center;
                padding: 0.44rem 0.62rem;
                font-size: 0.74rem;
                gap: 0.34rem;
            }

            .product-share-tooltip {
                left: 0;
                right: auto;
                transform: translateX(0) scale(0.96);
                width: max-content;
                min-width: 8.8rem;
                max-width: min(78vw, 12.2rem);
                min-width: 0;
                padding: 0.5rem 0.56rem;
                border-radius: 0.75rem;
            }

            .product-share-menu[open] .product-share-tooltip {
                transform: translateX(0) scale(1);
            }

            .product-share-tooltip::before {
                left: 1.2rem;
                transform: rotate(45deg);
                width: 0.72rem;
                height: 0.72rem;
                top: -0.36rem;
            }

            .product-share-icons {
                gap: 0.35rem;
            }

            .product-share-icon {
                width: 2rem;
                height: 2rem;
            }

            .product-share-icon svg {
                width: 0.92rem;
                height: 0.92rem;
            }

            .product-share-note {
                margin-top: 0.35rem;
                font-size: 0.62rem;
            }

            .product-zoom-controls {
                gap: 0.25rem;
                padding: 0.2rem;
            }

            .product-zoom-controls button[id^="product-zoom-"] {
                padding-left: 0.45rem;
                padding-right: 0.45rem;
            }
        }

        @media (max-width: 360px) {
            .product-share-toggle span {
                display: inline;
            }

            .product-share-toggle {
                padding: 0.42rem 0.55rem;
                min-width: 0;
            }

            .product-share-toggle .h-4.w-4 {
                width: 0.9rem;
                height: 0.9rem;
            }

            .product-zoom-controls {
                transform: scale(0.93);
                transform-origin: right center;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .product-share-toggle {
                animation: none;
            }
        }

        @keyframes product-share-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.36);
            }
            70% {
                box-shadow: 0 0 0 14px rgba(5, 150, 105, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(5, 150, 105, 0);
            }
        }
    </style>
@endpush

@section('content')
    @php
        $shareUrl = trim((string) ($canonicalProductUrl ?? route('products.show', $product->id)));
        $shareTitle = trim($product->code.' '.$product->name);
        $shareMessage = 'Lihat produk '.$shareTitle.' di katalog PT. Sidoagung Farm: '.$shareUrl;
        $whatsAppShareUrl = 'https://wa.me/?text='.urlencode($shareMessage);
        $facebookTargetUrl = route('products.share.facebook', $product->id);
        $facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($facebookTargetUrl);
        $shareHost = strtolower((string) parse_url($shareUrl, PHP_URL_HOST));
        $isLocalShareHost = in_array($shareHost, ['localhost', '127.0.0.1', '::1'], true);
    @endphp

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
                    <div class="product-preview-toolbar mb-3">
                        <details class="product-share-menu" id="product-share-menu">
                            <summary class="product-share-toggle">
                                <span>Share</span>
                                <x-lucide-share-2 class="h-4 w-4" />
                            </summary>
                            <div class="product-share-tooltip">
                                <div class="product-share-icons">
                                    <a
                                        href="{{ $whatsAppShareUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="product-share-icon product-share-icon--whatsapp"
                                        aria-label="Share ke WhatsApp"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M19.05 4.91A9.82 9.82 0 0 0 12.06 2a9.94 9.94 0 0 0-8.5 15.1L2 22l5.06-1.51A9.93 9.93 0 0 0 12.06 22c5.49 0 9.94-4.45 9.94-9.94 0-2.65-1.04-5.15-2.95-7.15ZM12.06 20.2c-1.53 0-3.03-.4-4.34-1.16l-.31-.18-3 .9.91-2.92-.2-.32a8.13 8.13 0 0 1 6.94-12.71c2.17 0 4.21.84 5.74 2.38a8.05 8.05 0 0 1 2.38 5.73c0 4.49-3.65 8.14-8.12 8.14Zm4.46-6.1c-.25-.12-1.47-.72-1.7-.81-.23-.08-.4-.12-.57.12s-.65.8-.8.96c-.15.17-.3.19-.56.06-.25-.12-1.07-.39-2.03-1.25-.74-.65-1.25-1.47-1.39-1.72-.14-.25-.02-.39.11-.51.11-.12.25-.3.37-.45.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.44-.06-.12-.57-1.37-.78-1.88-.2-.48-.41-.42-.57-.43h-.49c-.16 0-.43.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.9 2.4 1.02 2.57.12.17 1.76 2.67 4.27 3.74.6.26 1.07.42 1.44.54.61.19 1.17.16 1.62.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.09-.23-.15-.48-.27Z" />
                                        </svg>
                                    </a>
                                    <button
                                        type="button"
                                        id="product-share-instagram"
                                        data-share-url="{{ $shareUrl }}"
                                        data-share-title="{{ $shareTitle }}"
                                        data-share-text="{{ $shareMessage }}"
                                        class="product-share-icon product-share-icon--instagram"
                                        aria-label="Share ke Instagram"
                                        title="Akan membuka Instagram dan menyalin link produk"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M7.5 2C4.47 2 2 4.47 2 7.5v9C2 19.53 4.47 22 7.5 22h9c3.03 0 5.5-2.47 5.5-5.5v-9C22 4.47 19.53 2 16.5 2h-9Zm0 1.8h9c2.04 0 3.7 1.66 3.7 3.7v9c0 2.04-1.66 3.7-3.7 3.7h-9a3.7 3.7 0 0 1-3.7-3.7v-9c0-2.04 1.66-3.7 3.7-3.7Zm9.58 1.33a1.09 1.09 0 1 0 0 2.18 1.09 1.09 0 0 0 0-2.18ZM12 7a5 5 0 0 0-5 5 5 5 0 1 0 5-5Zm0 1.8A3.2 3.2 0 1 1 8.8 12 3.2 3.2 0 0 1 12 8.8Z" />
                                        </svg>
                                    </button>
                                    <a
                                        href="{{ $facebookShareUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="product-share-icon product-share-icon--facebook"
                                        aria-label="Share ke Facebook"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M13.5 21v-8h2.6l.4-3h-3V8.1c0-.87.25-1.46 1.49-1.46h1.59V3.96c-.28-.04-1.24-.12-2.35-.12-2.33 0-3.93 1.42-3.93 4.03V10H7.7v3h2.6v8h3.2Z" />
                                        </svg>
                                    </a>
                                </div>
                                @if($isLocalShareHost)
                                    <p class="product-share-note">Preview Facebook/Instagram tidak tampil di localhost.</p>
                                @endif
                            </div>
                        </details>
                        <div class="product-zoom-controls inline-flex items-center gap-2 rounded-lg bg-white/90 p-1 ring-1 ring-slate-200">
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
                            decoding="async"
                            fetchpriority="high"
                            width="600"
                            height="900"
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
                    <p class="mx-auto max-w-3xl whitespace-pre-line catalog-text-justify text-sm leading-relaxed text-slate-700">{!! nl2br(e($product->description)) !!}</p>
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
                    decoding="async"
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
                                                src="{{ $relatedProduct->image?->thumbnail_path ?? $relatedProduct->image?->system_path ?? 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image' }}"
                                                alt="{{ $relatedProduct->code }}"
                                                class="h-full w-full object-cover catalog-lazy-image transition-opacity duration-300"
                                                data-lazy-image
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                width="120"
                                                height="180"
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

    @include('partials.whatsapp-floating-button')
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
            const shareMenu = document.getElementById('product-share-menu');
            const instagramShareButton = document.getElementById('product-share-instagram');

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

            if (shareMenu) {
                document.addEventListener('click', (event) => {
                    if (shareMenu.open && !shareMenu.contains(event.target)) {
                        shareMenu.open = false;
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        shareMenu.open = false;
                    }
                });
            }

            if (instagramShareButton) {
                instagramShareButton.addEventListener('click', async () => {
                    const shareUrl = instagramShareButton.dataset.shareUrl || window.location.href;
                    const shareText = instagramShareButton.dataset.shareText || '';
                    const shareTitle = instagramShareButton.dataset.shareTitle || document.title;

                    if (navigator.share) {
                        try {
                            await navigator.share({
                                title: shareTitle,
                                text: shareText,
                                url: shareUrl,
                            });
                            return;
                        } catch (error) {
                            // Fallback below when Web Share is cancelled/unsupported.
                        }
                    }

                    if (navigator.clipboard?.writeText) {
                        try {
                            await navigator.clipboard.writeText(shareUrl);
                        } catch (error) {
                            // Ignore clipboard failure; Instagram fallback still opens.
                        }
                    }

                    window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
                });
            }
        })();
    </script>
@endpush
