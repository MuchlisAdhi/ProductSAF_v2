@extends('layouts.app')

@section('content')
    <section class="catalog-page space-y-5">
        <section id="top" class="catalog-panel relative overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <div class="swiper hero-swiper catalog-hero-swiper h-[320px] sm:h-[400px] lg:h-[500px] 2xl:h-[560px]">
                <div class="swiper-wrapper">
                    @foreach(['/images/bg-office.jpeg', '/images/bg-silo1.jpeg', '/images/bg-silo2.jpeg', '/images/bg-silo3.jpeg'] as $slideImage)
                        <div class="swiper-slide">
                            <div class="h-full w-full">
                                <img src="{{ asset($slideImage) }}" alt="Sidoagung Hero Background" class="h-full w-full object-cover">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-white/25 via-white/10 to-transparent"></div>
                <!-- <div class="absolute left-0 top-0 z-20 p-5 sm:p-8 lg:p-10">
                    <span class="inline-flex items-center rounded-full border border-white bg-slate-600/80 px-4 py-2 text-white shadow-sm">
                        <span class="catalog-hero-chip-title text-lg font-semibold sm:text-xl">Katalog Produk</span>
                    </span>
                </div> -->
                <div class="swiper-pagination !bottom-3 z-30"></div>
            </div>
        </section>

        <section id="katalog" class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Kategori Produk</h2>
                    <p class="text-sm text-slate-600">Pilih kategori untuk menampilkan daftar produk.</p>
                </div>
                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Total Produk: {{ $totalProducts }}
                </span>
            </div>

            <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($categories as $category)
                    <a href="{{ route('categories.show', $category->id) }}" class="catalog-card group rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                                @include('partials.category-icon', [
                                    'icon' => $category->icon,
                                    'alt' => $category->name,
                                    'imgClass' => 'h-5 w-5 object-contain',
                                    'textClass' => 'text-[10px] font-semibold text-emerald-700',
                                ])
                            </span>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $category->products_count }}</span>
                        </div>
                        <p class="mt-4 text-sm font-semibold text-slate-900">{{ $category->name }}</p>
                        <p class="mt-1 text-xs text-slate-500 group-hover:text-emerald-700">Lihat produk</p>
                    </a>
                @empty
                    <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        Belum ada kategori.
                    </p>
                @endforelse
            </div>
        </section>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('.hero-swiper');
            if (!root || typeof window.Swiper === 'undefined') return;

            new window.Swiper('.hero-swiper', {
                loop: true,
                speed: 900,
                autoplay: {
                    delay: 3500,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true,
                },
            });
        })();
    </script>
@endpush
