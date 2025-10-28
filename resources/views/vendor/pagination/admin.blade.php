@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-info">
            <p>Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong>results</p>
        </div>

        <div class="pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true" aria-label="Previous">
                    <i class="ph ph-caret-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" 
                   class="pagination-btn" 
                   rel="prev" 
                   aria-label="Previous">
                    <i class="ph ph-caret-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-dots" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn" aria-label="Go to page {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" 
                   class="pagination-btn" 
                   rel="next" 
                   aria-label="Next">
                    <i class="ph ph-caret-right"></i>
                </a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true" aria-label="Next">
                    <i class="ph ph-caret-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
