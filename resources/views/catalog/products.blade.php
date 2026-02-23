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
                        Back
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
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $filteredCount }} produk
                </span>
            </div>
        </div>

        <div class="catalog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ $basePath }}" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-6">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Search</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Search code, name, description..."
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                    />
                </div>

                @if($categories->count() > 0)
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Category</label>
                        <select name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($categoryFilter === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Sack Color</label>
                    <select name="sackColor" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">All Colors</option>
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
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Sort By</label>
                    <select name="sort" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="latest" @selected($sort === 'latest')>Latest</option>
                        <option value="code_asc" @selected($sort === 'code_asc')>Code A-Z</option>
                        <option value="code_desc" @selected($sort === 'code_desc')>Code Z-A</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Name A-Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Name Z-A</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Rows</label>
                    <select name="pageSize" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        @foreach([6, 12, 24, 48] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-6 flex items-center justify-between gap-2">
                    <input type="hidden" name="page" value="1">
                    <a href="{{ $basePath }}" class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Reset filters</a>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600">Apply</button>
                </div>
            </form>

            <div class="p-4 sm:p-5">
                @if($products->count() === 0)
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                        No products found for current filters.
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($products as $product)
                            <a href="{{ route('products.show', $product->id) }}?returnTo={{ urlencode(request()->fullUrl()) }}" class="catalog-card group rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                                <div class="flex items-start gap-3">
                                    <div class="h-24 w-20 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        <img src="{{ $product->image?->system_path ?? 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image' }}" alt="{{ $product->code }}" class="h-full w-full object-cover">
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
                                        <p class="mt-2 line-clamp-2 text-xs text-slate-600">{{ $product->description }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/70 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-sm text-slate-600">
                    Showing <span class="font-semibold text-slate-900">{{ $products->count() }}</span>
                    of <span class="font-semibold text-slate-900">{{ $filteredCount }}</span> products
                    ({{ $totalCount }} total)
                </p>
                {{ $products->onEachSide(1)->links() }}
            </div>
        </div>
    </section>
@endsection
