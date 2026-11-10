@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pagination-nav">
        <div class="pagination-info">
            Menampilkan
            @if ($paginator->firstItem())
                <strong>{{ $paginator->firstItem() }}</strong>
                sampai
                <strong>{{ $paginator->lastItem() }}</strong>
            @else
                <strong>{{ $paginator->count() }}</strong>
            @endif
            dari
            <strong>{{ $paginator->total() }}</strong>
            hasil
        </div>

        <div class="pagination-controls">
            {{-- Tombol Halaman Sebelumnya / Kembali --}}
            @if ($paginator->onFirstPage())
                <span class="page-arrow disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-arrow" aria-label="Halaman sebelumnya">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Nomor-Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Separator titik tiga --}}
                @if (is_string($element))
                    <span class="page-arrow disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Tautan Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-num active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-num" aria-label="Buka halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Halaman Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-arrow" aria-label="Halaman selanjutnya">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="page-arrow disabled" aria-disabled="true" aria-label="Halaman selanjutnya">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
