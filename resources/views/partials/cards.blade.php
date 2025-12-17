@php
    $isPaginator = (isset($listings) && $listings instanceof \Illuminate\Pagination\LengthAwarePaginator);
    $items = $listings;
@endphp

<div class="row g-4" id="search-results-grid">
    @forelse($items as $listing)
        @include('partials.listing_card', ['listing' => $listing])
    @empty
        <div class="col-12">
            <div class="alert alert-info">No listings match your search.</div>
        </div>
    @endforelse
    @if($isPaginator && $listings->lastPage() > 1)
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        @if ($listings->onFirstPage())
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $listings->previousPageUrl() }}" rel="prev"><i class="fas fa-chevron-left"></i></a></li>
                        @endif

                        @foreach ($listings->getUrlRange(1, $listings->lastPage()) as $page => $url)
                            @if ($page == $listings->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if ($listings->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $listings->nextPageUrl() }}" rel="next"><i class="fas fa-chevron-right"></i></a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    @endif
</div>