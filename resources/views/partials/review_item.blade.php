@php
    $r = $review;
    $isHost = Auth::check() && Auth::id() === $r->host_id;
    $canRespond = $isHost && empty($r->answer_content);
    
    // Parse answered_at date safely
    $answeredAt = null;
    if (!empty($r->answered_at)) {
        $answeredAt = $r->answered_at instanceof \Carbon\Carbon 
            ? $r->answered_at 
            : \Illuminate\Support\Carbon::parse($r->answered_at);
    }
    
    $canEditResponse = $isHost && !empty($r->answer_content) && $answeredAt && $answeredAt->addDays(2) >= now();
@endphp

<div class="review-item p-3 rounded border">
    <div class="d-flex align-items-start gap-3">
        {{-- Profile Picture --}}
        <div class="flex-shrink-0">
            @if(!empty($r->guest_photo))
                
                <img src="{{ $r->guest_photo }}" 
                     alt="{{ $r->guest_name ?? 'Guest' }}" 
                     class="review-avatar rounded-circle"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($r->guest_name ?? 'Guest') }}&background=0d6efd&color=fff&size=128'">
            @else
                <div class="review-avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                    <span class="fw-bold">{{ strtoupper(substr($r->guest_name ?? 'G', 0, 1)) }}</span>
                </div>
            @endif
        </div>

        {{-- Review Content --}}
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <a href="{{ route('profile.public', $r->guest_id) }}" class="text-decoration-none fw-semibold" style="color: #722F37;">
                        <h6 class="mb-0 fw-bold">{{ $r->guest_name ?? 'Anonymous Guest' }}</h6>
                    </a>
                    
                    <div class="review-rating mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= ($r->rating ?? 0))
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-muted"></i>
                            @endif
                        @endfor
                        <span class="ms-1 text-muted small">{{ $r->rating ?? 0 }}/5</span>
                    </div>
                </div>
                <div class="text-muted small">
                    <i class="far fa-clock me-1"></i>
                    {{ isset($r->created_at) ? \Illuminate\Support\Carbon::parse($r->created_at)->diffForHumans() : '' }}
                </div>
            </div>

            @if(!empty($r->comment))
                <p class="mb-0 text-secondary">{{ $r->comment }}</p>
            @else
                <p class="mb-0 text-muted fst-italic">No written review provided.</p>
            @endif

            {{-- Host Response Section --}}
            @if(!empty($r->answer_content))
                <div class="host-response mt-3 p-3 bg-light rounded" id="response-{{ $r->id }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <strong class="small text-wine">{{ $host->full_name }} (Host)</strong>
                        @if(!empty($r->answered_at))
                            <span class="text-muted small ms-auto">
                                {{ $answeredAt ? $answeredAt->diffForHumans() : '' }}
                            </span>
                        @endif
                        
                        {{-- Edit/Delete buttons for host --}}
                        @if($canEditResponse)
                            <button type="button" class="btn btn-sm btn-link p-0 ms-2" 
                                    onclick="toggleEditResponse({{ $r->id }})"
                                    title="Edit response">
                                <i class="fas fa-edit text-secondary"></i>
                            </button>
                            <form action="{{ route('reviews.response.destroy', $r->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this response?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link p-0 ms-1" title="Delete response">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    {{-- Display Response --}}
                    <p class="mb-0 small" id="response-text-{{ $r->id }}">{{ $r->answer_content }}</p>
                    
                    {{-- Edit Response Form (Hidden by default) --}}
                    @if($canEditResponse)
                        <form action="{{ route('reviews.response.update', $r->id) }}" 
                              method="POST" 
                              class="mt-2" 
                              id="edit-response-form-{{ $r->id }}" 
                              style="display: none;">
                            @csrf
                            @method('PUT')
                            <textarea name="answer_content" 
                                      class="form-control form-control-sm mb-2" 
                                      rows="3" 
                                      maxlength="1000" 
                                      required>{{ $r->answer_content }}</textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                <button type="button" class="btn btn-secondary btn-sm" 
                                        onclick="toggleEditResponse({{ $r->id }})">Cancel</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Add Response Form (Only show if host hasn't responded yet) --}}
            @if($canRespond)
                <div class="mt-3">
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary" 
                            onclick="toggleResponseForm({{ $r->id }})">
                        <i class="fas fa-reply me-1"></i> Respond to Review
                    </button>
                    
                    <form action="{{ route('reviews.respond', $r->id) }}" 
                          method="POST" 
                          class="mt-2" 
                          id="response-form-{{ $r->id }}" 
                          style="display: none;">
                        @csrf
                        <textarea name="answer_content" 
                                  class="form-control form-control-sm mb-2" 
                                  rows="3" 
                                  maxlength="1000" 
                                  placeholder="Write your response..." 
                                  required></textarea>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Post Response</button>
                            <button type="button" class="btn btn-secondary btn-sm" 
                                    onclick="toggleResponseForm({{ $r->id }})">Cancel</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>


<script>
function toggleResponseForm(reviewId) {
    const form = document.getElementById(`response-form-${reviewId}`);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}

function toggleEditResponse(reviewId) {
    const text = document.getElementById(`response-text-${reviewId}`);
    const form = document.getElementById(`edit-response-form-${reviewId}`);
    
    if (text && form) {
        const isFormVisible = form.style.display !== 'none';
        text.style.display = isFormVisible ? 'block' : 'none';
        form.style.display = isFormVisible ? 'none' : 'block';
    }
}
</script>