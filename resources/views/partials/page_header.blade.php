<div class="card shadow-sm mb-4">
    <div class="card-body">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                @foreach($breadcrumbs as $breadcrumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $breadcrumb['title'] }}
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
        
        <!-- Header Content -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0 text-wine">{{ $title }}</h1>
            @if(isset($stats))
            <div class="d-flex gap-3 align-items-center">
                @foreach($stats as $stat)
                <div class="text-center">
                    <small class="text-muted">{{ $stat['label'] }}</small>
                    <h5>{{ $stat['value'] }}</h5>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>