@extends('layouts.app')

@section('content')
    <section class="catalog-page space-y-5">
        <div class="catalog-panel rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <a href="{{ $backHref }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                <x-lucide-arrow-left class="h-4 w-4" />
                {{ $backLabel }}
            </a>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-2xl font-semibold tracking-tight text-emerald-700 sm:text-3xl">{{ $product->code }}</p>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Detail produk & kandungan nutrisi.</p>
                    <x-sack-color-badge :color="$product->sack_color" variant="outline" class="mt-2" />
                </div>
                <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                    {{ $product->category->name }}
                </span>
            </div>
        </div>

        <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Preview Produk</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-100 via-white to-slate-200 p-4 sm:p-6">
                    <div class="mb-3 flex flex-wrap items-center justify-end gap-3">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-white/90 p-1 ring-1 ring-slate-200">
                            <button type="button" id="product-zoom-out" class="rounded-md p-2 text-slate-700 transition hover:bg-slate-100" aria-label="Zoom out">
                                <x-lucide-zoom-out class="h-4 w-4" />
                            </button>
                            <button type="button" id="product-zoom-reset" class="rounded-md px-2 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">100%</button>
                            <button type="button" id="product-zoom-in" class="rounded-md p-2 text-slate-700 transition hover:bg-slate-100" aria-label="Zoom in">
                                <x-lucide-zoom-in class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div id="product-zoom-stage" class="mx-auto grid max-w-2xl place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white p-6 touch-none">
                        <img
                            id="zoomable-product-image"
                            src="{{ $product->image?->system_path ?? 'https://placehold.co/300x450/e2e8f0/334155?text=No+Image' }}"
                            alt="{{ $product->code }}"
                            class="h-auto w-full max-w-[30rem] origin-center object-contain transition-transform duration-200 ease-out will-change-transform"
                            loading="eager"
                            draggable="false"
                        >
                    </div>
                    <p class="mt-2 text-center text-xs text-slate-500">Gunakan tombol zoom atau scroll mouse di atas gambar.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                    <x-lucide-tag class="h-5 w-5" />
                </span>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Code</p>
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

            if (!image || !stage || !zoomInButton || !zoomOutButton || !resetButton) return;

            const minScale = 1;
            const maxScale = 3;
            const step = 0.2;
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
                image.style.transform = `translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
                resetButton.textContent = `${Math.round(scale * 100)}%`;
                stage.classList.toggle('cursor-grab', scale > 1 && !isDragging);
                stage.classList.toggle('cursor-grabbing', isDragging);
            };

            const setScale = (nextScale) => {
                scale = clamp(nextScale, minScale, maxScale);
                if (scale === 1) {
                    offsetX = 0;
                    offsetY = 0;
                }
                applyScale();
            };

            zoomInButton.addEventListener('click', () => {
                setScale(scale + step);
            });

            zoomOutButton.addEventListener('click', () => {
                setScale(scale - step);
            });

            resetButton.addEventListener('click', () => {
                setScale(1);
            });

            stage.addEventListener('wheel', (event) => {
                event.preventDefault();
                setScale(scale + (event.deltaY < 0 ? step : -step));
            }, { passive: false });

            stage.addEventListener('pointerdown', (event) => {
                if (scale <= 1) return;

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
        })();
    </script>
@endpush
