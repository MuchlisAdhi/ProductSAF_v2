@extends('layouts.app')

@section('content')
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ $backHref }}" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-amber-300/80">
                    <x-lucide-arrow-left class="h-4 w-4" />
                    Back
                </a>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">{{ $filteredCount }} produk</span>
            </div>
            <h1 class="mt-3 text-lg font-semibold text-slate-900">{{ $title }}</h1>
            <p class="text-xs text-slate-600 sm:text-sm">{{ $subtitle }}</p>
            @if($categoryMeta)
                <div class="mt-1 inline-flex items-center gap-2 rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">
                    @include('partials.category-icon', [
                        'icon' => $categoryMeta['icon'],
                        'alt' => $categoryMeta['name'],
                        'imgClass' => 'h-4 w-4 text-sky-700',
                        'textClass' => 'text-[10px] font-semibold text-sky-700',
                    ])
                    <span>{{ $categoryMeta['name'] }}</span>
                </div>
            @endif
        </div>

        <div class="border-b border-slate-200 bg-white p-4 sm:p-6">
            <form method="GET" action="{{ $basePath }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Search</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Search code, name, description..."
                        class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80"
                    />
                </div>

                @if($categories->count() > 0)
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Category</label>
                        <select name="category" class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($categoryFilter === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Sack Color</label>
                    <select name="sackColor" class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80">
                        <option value="">All Colors</option>
                        @foreach($sackColors as $color)
                            <option value="{{ $color }}" @selected($sackColorFilter === $color)>{{ $color }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Rows</label>
                        <select name="pageSize" class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80">
                            @foreach([6, 12, 24, 48] as $size)
                                <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <input type="hidden" name="page" value="1">
                        <button type="submit" class="min-h-[44px] w-full rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Apply</button>
                    </div>
                </div>
            </form>

            <div class="mt-3 flex justify-end">
                <a href="{{ $basePath }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Reset filters</a>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            @if($products->count() === 0)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">No products found for current filters.</div>
            @else
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product->id) }}?returnTo={{ urlencode(request()->fullUrl()) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-300/80">
                            <div class="flex items-start gap-4">
                                <div class="w-16 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                    <img src="{{ $product->image?->system_path ?? 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image' }}" alt="{{ $product->code }}" class="h-20 w-16 object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-emerald-700">{{ $product->code }}</p>
                                    <p class="mt-0.5 line-clamp-2 text-sm font-semibold text-slate-900">{{ $product->name }}</p>
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

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="text-sm text-slate-600">
                Showing <span class="font-semibold text-slate-900">{{ $products->count() }}</span>
                of <span class="font-semibold text-slate-900">{{ $filteredCount }}</span> products
                ({{ $totalCount }} total)
            </p>
            {{ $products->onEachSide(1)->links() }}
        </div>
    </section>
@endsection
