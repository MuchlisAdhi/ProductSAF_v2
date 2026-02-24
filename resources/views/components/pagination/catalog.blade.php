@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $mobileStartPage = max(1, $currentPage - 1);
        $mobileEndPage = min($lastPage, $currentPage + 1);
    @endphp

    <nav role="navigation" aria-label="Navigasi pagination" class="w-full">
        <div class="space-y-2 sm:hidden">
            <p class="text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Halaman {{ $currentPage }} dari {{ $lastPage }}
            </p>

            <div class="grid grid-cols-2 gap-2">
                @if ($paginator->onFirstPage())
                    <span class="pagination-emerald-ring inline-flex h-10 items-center justify-center rounded-xl bg-[darkseagreen] text-slate-700 opacity-60" aria-hidden="true">
                        &lt;-
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-emerald-ring inline-flex h-10 items-center justify-center rounded-xl bg-[darkseagreen] text-slate-900 shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Halaman sebelumnya">
                        &lt;-
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-emerald-ring inline-flex h-10 items-center justify-center rounded-xl bg-[darkseagreen] text-slate-900 shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Halaman selanjutnya">
                        -&gt;
                    </a>
                @else
                    <span class="pagination-emerald-ring inline-flex h-10 items-center justify-center rounded-xl bg-[darkseagreen] text-slate-700 opacity-60" aria-hidden="true">
                        -&gt;
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto pb-1">
                <div class="pagination-emerald-ring mx-auto inline-flex min-w-max items-center gap-1 rounded-xl bg-slate-900 p-1 shadow-sm">
                    @if ($mobileStartPage > 1)
                        <a href="{{ $paginator->url(1) }}" class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-xs font-semibold text-slate-200 transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Ke halaman 1">1</a>
                        @if ($mobileStartPage > 2)
                            <span class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-xs font-semibold text-slate-400" aria-hidden="true">&hellip;</span>
                        @endif
                    @endif

                    @for ($page = $mobileStartPage; $page <= $mobileEndPage; $page++)
                        @if ($page === $currentPage)
                            <span aria-current="page" class="grid h-8 min-w-8 place-items-center rounded-lg bg-[var(--color-emerald-700)] px-2 text-xs font-bold text-white shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $paginator->url($page) }}" class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-xs font-semibold text-slate-200 transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Ke halaman {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    @if ($mobileEndPage < $lastPage)
                        @if ($mobileEndPage < ($lastPage - 1))
                            <span class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-xs font-semibold text-slate-400" aria-hidden="true">&hellip;</span>
                        @endif
                        <a href="{{ $paginator->url($lastPage) }}" class="grid h-8 min-w-8 place-items-center rounded-lg px-2 text-xs font-semibold text-slate-200 transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Ke halaman {{ $lastPage }}">
                            {{ $lastPage }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="hidden items-center justify-end sm:flex">
            <div class="pagination-emerald-ring inline-flex items-center gap-1 rounded-2xl bg-slate-900 p-1.5 shadow-lg">
                @if ($paginator->onFirstPage())
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-[darkseagreen] text-slate-700 opacity-60" aria-hidden="true">&larr;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid h-9 w-9 place-items-center rounded-xl bg-[darkseagreen] text-slate-900 transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Halaman sebelumnya">
                        &larr;
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="grid h-9 min-w-9 place-items-center rounded-xl px-2 text-sm font-semibold text-slate-400" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="grid h-9 min-w-9 place-items-center rounded-xl bg-[var(--color-emerald-700)] px-2 text-sm font-bold text-white shadow-sm">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="grid h-9 min-w-9 place-items-center rounded-xl px-2 text-sm font-semibold text-slate-200 transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Ke halaman {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid h-9 w-9 place-items-center rounded-xl bg-[darkseagreen] text-slate-900 transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-emerald-700)]" aria-label="Halaman selanjutnya">
                        &rarr;
                    </a>
                @else
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-[darkseagreen] text-slate-700 opacity-60" aria-hidden="true">&rarr;</span>
                @endif
            </div>
        </div>
    </nav>
@endif
