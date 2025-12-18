<div >
    <div class="py-2">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 ">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="text-white">Home</a>
                </li>
                @foreach($breadcrumbs as $breadcrumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            {{ $breadcrumb['title'] }}
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $breadcrumb['url'] }}" class="text-white">{{ $breadcrumb['title'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
        
        <!-- Header Content -->
        @if(isset($stats))
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <div class="d-flex gap-3 align-items-center">
                @foreach($stats as $stat)
                <div class="text-center">
                    <small class="text-muted">{{ $stat['label'] }}</small>
                    <h5>{{ $stat['value'] }}</h5>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Custom breadcrumb styling */

.breadcrumb-item + .breadcrumb-item::before {
    content: "/" !important;
    color: white;
    padding: 0 0.5rem;
}

.breadcrumb-item a {
    text-decoration: none;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-item.active {
    color:rgb(157, 157, 157) !important;
}
</style>