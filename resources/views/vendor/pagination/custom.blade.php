@if ($paginator->hasPages())
    <div class="pagination-minimalist-layout w-full flex flex-col md:grid md:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] md:items-center md:gap-4 md:px-4 md:py-6">
        <div class="hidden md:block" aria-hidden="true"></div>

        <nav class="pagination-minimalist flex items-center justify-center gap-2 px-4 py-6 md:px-0 md:py-0" aria-label="Pagination Navigation">
            @if ($paginator->onFirstPage())
                <button
                    disabled
                    class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-lg bg-gray-100 text-gray-400 transition-all duration-200"
                    aria-disabled="true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#1b6b3d] text-white shadow-sm transition-all duration-200 hover:bg-[#155233] hover:shadow-md"
                    rel="prev"
                    aria-label="Previous page"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-2 text-sm text-gray-500">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <button
                                    disabled
                                    class="flex h-10 w-10 cursor-default items-center justify-center rounded-lg bg-[#1b6b3d] text-sm font-semibold text-white shadow-md"
                                    aria-label="Page {{ $page }}"
                                    aria-current="page"
                                >
                                    {{ $page }}
                                </button>
                            @else
                                <a
                                    href="{{ $url }}"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-[#1b6b3d] transition-all duration-200 hover:border-[#1b6b3d] hover:bg-green-50"
                                    aria-label="Go to page {{ $page }}"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-2 sm:hidden">
                <span class="text-sm font-medium text-gray-600">
                    Hal. {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
                </span>
            </div>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#1b6b3d] text-white shadow-sm transition-all duration-200 hover:bg-[#155233] hover:shadow-md"
                    rel="next"
                    aria-label="Next page"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @else
                <button
                    disabled
                    class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-lg bg-gray-100 text-gray-400 transition-all duration-200"
                    aria-disabled="true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif
        </nav>

        <div class="pagination-minimalist-info flex items-center justify-end px-4 pb-3 text-right md:px-0 md:pb-0">
            <p class="text-sm text-gray-600">
                Menampilkan
                <span class="font-semibold text-[#1b6b3d]">{{ $paginator->firstItem() ?? 0 }}</span>
                -
                <span class="font-semibold text-[#1b6b3d]">{{ $paginator->lastItem() ?? 0 }}</span>
                dari
                <span class="font-semibold text-[#1b6b3d]">{{ $paginator->total() }}</span>
                data
            </p>
        </div>
    </div>

    <style>
        .pagination-minimalist button,
        .pagination-minimalist a {
            outline: none;
        }

        .pagination-minimalist button:focus-visible,
        .pagination-minimalist a:focus-visible {
            outline: 2px solid #1b6b3d;
            outline-offset: 2px;
        }

        @media (max-width: 640px) {
            .pagination-minimalist {
                gap: 8px;
                padding: 1rem;
            }

            .pagination-minimalist button,
            .pagination-minimalist a {
                width: 40px;
                height: 40px;
                min-width: 40px;
                min-height: 40px;
            }
        }

        @media (min-width: 641px) and (max-width: 768px) {
            .pagination-minimalist {
                gap: 6px;
            }

            .pagination-minimalist button,
            .pagination-minimalist a {
                width: 36px;
                height: 36px;
            }
        }
    </style>
@endif
