@extends('layouts.app')

@section('content')
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-4 py-4 sm:px-6">
            <a href="{{ $backHref }}" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-amber-300/80">
                <x-lucide-arrow-left class="h-4 w-4" />
                {{ $backLabel }}
            </a>
            <div class="mt-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-emerald-700">{{ $product->code }}</p>
                    <h1 class="mt-1 text-lg font-semibold text-slate-900">{{ $product->name }}</h1>
                    <p class="text-xs text-slate-600">Detail produk & kandungan nutrisi.</p>
                    <x-sack-color-badge :color="$product->sack_color" variant="outline" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 px-4 py-8 sm:px-6">
                    <div class="mx-auto mb-4 flex w-full max-w-4xl justify-start">
                        <div class="rounded-full bg-sky-100/90 px-3 py-1 text-xs font-semibold text-sky-800 shadow-sm ring-1 ring-sky-200">
                            {{ $product->category->name }}
                        </div>
                    </div>
                    <img src="{{ $product->image?->system_path ?? 'https://placehold.co/300x450/e2e8f0/334155?text=No+Image' }}" alt="{{ $product->code }}" class="mx-auto h-auto w-full object-contain drop-shadow-md" style="max-width: 14rem;" loading="eager">
                </div>

                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <p class="text-sm text-slate-600">{{ $product->description }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-emerald-50 p-4 text-center">
                            <x-lucide-tag class="mx-auto text-emerald-700" style="width:1.25rem;height:1.25rem;stroke-width:2.5;" />
                            <p class="mt-1 text-[10px] text-slate-500">Kode</p>
                            <p class="text-sm font-extrabold text-slate-900">{{ $product->code }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-4 text-center">
                            <x-lucide-box class="mx-auto text-amber-800" style="width:1.25rem;height:1.25rem;stroke-width:2.5;" />
                            <p class="mt-1 text-[10px] text-slate-500">Warna Karung</p>
                            <p class="mt-1">
                                <x-sack-color-badge :color="$product->sack_color" variant="outline" />
                            </p>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background-color: #e0f2fe;">
                            <x-lucide-layers class="mx-auto text-sky-800" style="width:1.25rem;height:1.25rem;stroke-width:2.5;" />
                            <p class="mt-1 text-[10px] text-slate-500">Kategori</p>
                            <p class="text-sm font-extrabold text-slate-900">{{ $product->category->name }}</p>
                        </div>
                    </div>

                    <div class="mt-14 pb-3 sm:mt-16" style="margin-top: 1.5rem;">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                            <x-lucide-clipboard-list class="h-5 w-5 text-emerald-700" />
                            Kandungan Nutrisi
                        </h3>

                        <div class="mt-8 rounded-2xl border border-slate-200 shadow-sm" style="margin-top: 1.5rem;">
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed">
                                <colgroup>
                                    <col class="w-[62%] sm:w-[68%]">
                                    <col class="w-[38%] sm:w-[32%]">
                                </colgroup>
                                <thead>
                                    <tr class="bg-emerald-700 text-white">
                                        <th class="px-4 py-3 text-left text-sm font-semibold sm:px-8 sm:py-4 sm:text-base">Parameter</th>
                                        <th class="px-4 py-3 text-right text-sm font-semibold sm:px-8 sm:py-4 sm:text-base">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($product->nutritions as $index => $nutrition)
                                        <tr
                                            class="border-t border-slate-200 {{ $loop->even ? 'bg-emerald-50/40' : 'bg-white' }}"
                                            @if($loop->even) style="background-color: rgb(236 253 245 / 0.4);" @endif
                                        >
                                            <td class="break-words px-4 py-3 text-sm leading-6 text-slate-600 sm:px-8 sm:py-4 sm:text-base sm:leading-7">{{ $nutrition->label }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-semibold leading-6 text-slate-900 sm:px-8 sm:py-4 sm:text-base sm:leading-7 sm:whitespace-nowrap">{{ $nutrition->value }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-sm text-slate-600 sm:px-8 sm:py-7 sm:text-base">Tidak ada data nutrisi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
