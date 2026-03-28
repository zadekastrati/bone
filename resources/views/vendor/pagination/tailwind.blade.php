@if ($paginator->hasPages())
    @php
        $lastPage = max(1, $paginator->lastPage());
        $progressPct = $lastPage <= 1 ? 100 : min(100, ($paginator->currentPage() / $lastPage) * 100);
    @endphp
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-nav">
        <div class="pagination-nav__surface">
            {{-- Position within result set --}}
            <div
                class="pagination-nav__track"
                aria-hidden="true"
            >
                <div
                    class="pagination-nav__progress"
                    style="width: {{ $progressPct }}%"
                ></div>
            </div>

            <div class="pagination-nav__layout">
                <p class="pagination-nav__meta">
                    @if ($paginator->firstItem())
                        <span class="pagination-nav__meta-strong">{{ $paginator->firstItem() }}</span>
                        <span class="pagination-nav__meta-dash">{{ __('to') }}</span>
                        <span class="pagination-nav__meta-strong">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="pagination-nav__meta-strong">{{ $paginator->count() }}</span>
                    @endif
                    <span class="pagination-nav__meta-of">{{ __('of') }}</span>
                    <span class="pagination-nav__meta-strong">{{ $paginator->total() }}</span>
                    <span class="pagination-nav__meta-label">{{ __('results') }}</span>
                </p>

                {{-- Mobile: prev / next as prominent controls --}}
                <div class="pagination-nav__mobile flex w-full justify-between gap-3 sm:hidden">
                    @if ($paginator->onFirstPage())
                        <span class="pagination-nav__mob-btn pagination-nav__mob-btn--disabled">
                            <svg class="pagination-nav__chev" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>{{ __('pagination.previous') }}</span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-nav__mob-btn">
                            <svg class="pagination-nav__chev" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <span>{{ __('pagination.previous') }}</span>
                        </a>
                    @endif

                    <p class="pagination-nav__page-indicator" aria-current="true">
                        <span class="font-mono tabular-nums">{{ $paginator->currentPage() }}</span>
                        <span class="pagination-nav__page-indicator-sep">/</span>
                        <span class="pagination-nav__page-indicator-total font-mono tabular-nums">{{ $lastPage }}</span>
                    </p>

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-nav__mob-btn">
                            <span>{{ __('pagination.next') }}</span>
                            <svg class="pagination-nav__chev" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                    @else
                        <span class="pagination-nav__mob-btn pagination-nav__mob-btn--disabled">
                            <span>{{ __('pagination.next') }}</span>
                            <svg class="pagination-nav__chev" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </span>
                    @endif
                </div>

                {{-- sm+: pill rail --}}
                <div class="pagination-nav__desktop hidden sm:flex sm:items-center sm:justify-center">
                    <div class="pagination-nav__rail inline-flex flex-wrap items-center justify-center gap-1.5">
                        @if ($paginator->onFirstPage())
                            <span class="pagination-nav__icon-btn pagination-nav__icon-btn--disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-nav__icon-btn" aria-label="{{ __('pagination.previous') }}">
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </a>
                        @endif

                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span class="pagination-nav__ellipsis" aria-hidden="true">{{ $element }}</span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page" class="pagination-nav__pill pagination-nav__pill--active">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="pagination-nav__pill" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-nav__icon-btn" aria-label="{{ __('pagination.next') }}">
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                            </a>
                        @else
                            <span class="pagination-nav__icon-btn pagination-nav__icon-btn--disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
@endif
