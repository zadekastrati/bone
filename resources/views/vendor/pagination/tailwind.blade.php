@if ($paginator->hasPages())
    @php
        $lastPage = max(1, $paginator->lastPage());
    @endphp
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-nav w-full">
        <div class="pagination-row">
            <p class="pagination-row__meta">
                @if ($paginator->firstItem())
                    <span class="tabular-nums font-medium text-ink-700">{{ $paginator->firstItem() }}</span>
                    <span class="mx-1 text-ink-400">{{ __('to') }}</span>
                    <span class="tabular-nums font-medium text-ink-700">{{ $paginator->lastItem() }}</span>
                @else
                    <span class="tabular-nums font-medium text-ink-700">{{ $paginator->count() }}</span>
                @endif
                <span class="mx-1.5 text-ink-400">{{ __('of') }}</span>
                <span class="tabular-nums font-medium text-ink-700">{{ $paginator->total() }}</span>
                <span class="ml-1.5 text-ink-500">{{ __('results') }}</span>
            </p>

            <div class="pagination-row__controls">
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
    </nav>
@endif
