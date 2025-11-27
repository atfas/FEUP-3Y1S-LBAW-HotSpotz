@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h4>Edit Listing</h4>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('listing.update', ['id' => $listing->id]) }}" enctype="multipart/form-data" id="editListingForm">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $listing->description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Max occupancy</label>
                        <input type="number" name="max_occupancy" class="form-control" min="1" value="{{ old('max_occupancy', $listing->max_occupancy) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Minimum stay (nights)</label>
                        <input type="number" name="minimum_stay" class="form-control" min="1" value="{{ old('minimum_stay', $listing->minimum_stay) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location address</label>
                        <input type="text" name="location_address" class="form-control" value="{{ old('location_address', $listing->location_address) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cancellation policy</label>
                    <textarea name="cancellation_policy" class="form-control" rows="2">{{ old('cancellation_policy', $listing->cancellation_policy) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Existing Photos</label>
                    <div class="row g-2">
                        @foreach($photos as $p)
                            <div class="col-auto text-center">
                                <img src="{{ $p->url }}" class="img-thumbnail" style="width:120px;height:80px;object-fit:cover;display:block;">
                                <div class="form-check mt-1">
                                    <input class="form-check-input remove-photo-checkbox" type="checkbox" name="remove_photos[]" value="{{ $p->id }}" id="remove_photo_{{ $p->id }}">
                                    <label class="form-check-label small" for="remove_photo_{{ $p->id }}">Remove</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">You can remove existing photos. After removals and new uploads the total must be at least 3.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Add Photos (optional)</label>
                    <input type="file" name="photos[]" class="form-control" accept="image/*" multiple id="photosInput">
                    <div class="form-text">Upload additional images. Max size per image: 5MB.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tags (select at least one)</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach(($allTags ?? []) as $t)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $t->id }}" id="tag_{{ $t->id }}" @if(in_array($t->id, $selectedTags ?? [])) checked @endif>
                                <label class="form-check-label small" for="tag_{{ $t->id }}">{{ $t->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Choose at least one tag describing this listing.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amenities (comma-separated)</label>
                    <input type="text" name="amenities" class="form-control" value="{{ old('amenities', $listing->amenities ?? '') }}" placeholder="wifi,pool,parking">
                    <div class="form-text">Enter amenities as a comma-separated list, e.g. <code>wifi,pool</code>.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Availability</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="openAvailabilityBtn" data-bs-toggle="modal" data-bs-target="#availabilityModal">Edit Availabilities</button>
                        <div class="form-text">Edit availability ranges. There must be at least one availability.</div>
                    </div>
                    <input type="hidden" name="availabilities" id="availabilitiesInput" value="{{ old('availabilities') }}">
                </div>

                <div class="mb-3">
                    <h6>Current Availabilities</h6>
                    <div id="availabilitiesListContainer">
                        <div class="text-muted">No availabilities added yet.</div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        const form = document.getElementById('editListingForm');
        const photosInput = document.getElementById('photosInput');
        form.addEventListener('submit', function(e){
            const existingCount = {{ count($photos) }};
            const removeChecked = document.querySelectorAll('.remove-photo-checkbox:checked').length;
            const newFiles = photosInput.files ? photosInput.files.length : 0;
            if ((existingCount - removeChecked + newFiles) < 3) {
                e.preventDefault();
                alert('A listing must have at least 3 photos. Please upload more photos or uncheck removals.');
                return;
            }
            const availInput = document.getElementById('availabilitiesInput');
            try {
                const arr = availInput.value ? JSON.parse(availInput.value) : [];
                if (!Array.isArray(arr) || arr.length < 1) {
                    e.preventDefault();
                    alert('Please keep at least one availability.');
                    return;
                }
            } catch(err){
                e.preventDefault();
                alert('Invalid availabilities data.');
                return;
            }
            const checkedTags = form.querySelectorAll('input[name="tags[]"]:checked').length;
            if (checkedTags < 1) { e.preventDefault(); alert('Please select at least one tag for the listing.'); return; }
        });
    })();
</script>
@endpush

@push('scripts')
<script>
    (function(){
        function initThumbs(){
            const openBtn = document.getElementById('openAvailabilityBtn');
            const availInput = document.getElementById('availabilitiesInput');
            const listContainer = document.getElementById('availabilitiesListContainer');

            let availabilities = [];
            const perPage = 10;

            function renderList(page = 1) {
                if (!availabilities.length) {
                    listContainer.innerHTML = '<div class="text-muted">No availabilities added yet.</div>';
                    return;
                }
                const start = (page-1)*perPage;
                const pageItems = availabilities.slice(start, start+perPage);
                let html = '<ul class="list-group mb-2">';
                pageItems.forEach((a, idx) => {
                    const globalIndex = start + idx;
                    html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${a.start_date}</strong> → <strong>${a.end_date}</strong>
                            <div class="small text-muted">Price: $${parseFloat(a.price_per_night).toFixed(2)} — Check-in: ${a.check_in_time} — Check-out: ${a.check_out_time}</div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-edit-index="${globalIndex}">Edit</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-index="${globalIndex}">Remove</button>
                        </div>
                    </li>`;
                });
                html += '</ul>';

                const totalPages = Math.ceil(availabilities.length / perPage);
                if (totalPages > 1) {
                    html += '<nav><ul class="pagination">';
                    for (let p=1;p<=totalPages;p++){
                        html += `<li class="page-item ${p===page? 'active':''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                    }
                    html += '</ul></nav>';
                }

                listContainer.innerHTML = html;
            }

            function saveToHiddenInput(){
                availInput.value = JSON.stringify(availabilities);
            }

            const modalHtml = `
    <div class="modal fade" id="availabilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Edit Availability</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
            <label class="form-label">Start date</label>
            <input type="date" id="av_start" class="form-control">
            </div>
            <div class="mb-3">
            <label class="form-label">End date</label>
            <input type="date" id="av_end" class="form-control">
            </div>
            <div class="mb-3">
            <label class="form-label">Price per night</label>
            <input type="number" id="av_price" class="form-control" step="0.01" min="0">
            </div>
            <div class="mb-3">
            <label class="form-label">Check-in time</label>
            <input type="time" id="av_checkin" class="form-control">
            </div>
            <div class="mb-3">
            <label class="form-label">Check-out time</label>
            <input type="time" id="av_checkout" class="form-control">
            </div>
            <div class="text-danger small" id="av_error" style="display:none"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="av_add_btn">Add</button>
        </div>
        <div class="w-100 text-center"><div id="av_success" class="text-success small mt-2" style="display:none"></div></div>
        </div>
    </div>
    </div>`;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const availModalEl = document.getElementById('availabilityModal');
            function getModalInstance() {
                try {
                    if (window.bootstrap && bootstrap.Modal) {
                        if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                            return bootstrap.Modal.getOrCreateInstance(availModalEl);
                        }
                        if (typeof bootstrap.Modal.getInstance === 'function') {
                            return bootstrap.Modal.getInstance(availModalEl) || new bootstrap.Modal(availModalEl);
                        }
                        return new bootstrap.Modal(availModalEl);
                    }
                } catch (e) {
                    console.debug('bootstrap modal creation failed', e);
                }
                return null;
            }

            function removeModalBackdrop() {
                try {
                    document.querySelectorAll('.modal-backdrop').forEach(function(el){ el.parentNode && el.parentNode.removeChild(el); });
                    document.body.classList.remove('modal-open');
                } catch(e) { console.debug('removeModalBackdrop failed', e); }
            }

            function todayISO(){
                const d = new Date();
                const y = d.getFullYear();
                const m = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                return `${y}-${m}-${dd}`;
            }

            document.addEventListener('click', function(e){
                const rem = e.target.closest('[data-remove-index]');
                if (rem) {
                    const idx = parseInt(rem.getAttribute('data-remove-index'));
                    if (!isNaN(idx)) {
                        availabilities.splice(idx,1);
                        saveToHiddenInput();
                        renderList();
                    }
                }
                const editBtn = e.target.closest('[data-edit-index]');
                if (editBtn) {
                    const idx = parseInt(editBtn.getAttribute('data-edit-index'));
                    if (!isNaN(idx) && availabilities[idx]) {
                        const item = availabilities[idx];
                        availModalEl.querySelector('#av_start').value = item.start_date;
                        availModalEl.querySelector('#av_end').value = item.end_date;
                        availModalEl.querySelector('#av_price').value = item.price_per_night;
                        availModalEl.querySelector('#av_checkin').value = item.check_in_time;
                        availModalEl.querySelector('#av_checkout').value = item.check_out_time;
                        availModalEl.querySelector('#av_error').style.display = 'none';
                        availModalEl.setAttribute('data-editing-index', String(idx));
                        availModalEl.querySelector('#av_add_btn').textContent = 'Save';
                        const modalInst = getModalInstance();
                        if (modalInst) modalInst.show();
                        else { availModalEl.classList.add('show'); availModalEl.style.display='block'; }
                    }
                }
                const pageLink = e.target.closest('#availabilitiesListContainer a.page-link');
                if (pageLink) {
                    e.preventDefault();
                    const p = parseInt(pageLink.getAttribute('data-page')) || 1;
                    renderList(p);
                }
            });

            openBtn.addEventListener('click', function(){
                availModalEl.querySelector('#av_start').value = '';
                availModalEl.querySelector('#av_end').value = '';
                availModalEl.querySelector('#av_price').value = '';
                availModalEl.querySelector('#av_checkin').value = '';
                availModalEl.querySelector('#av_checkout').value = '';
                availModalEl.querySelector('#av_error').style.display = 'none';
                const modalInst = getModalInstance();
                if (modalInst) modalInst.show();
                else { availModalEl.classList.add('show'); availModalEl.style.display='block'; }
            });

            document.getElementById('av_add_btn').addEventListener('click', function(){
                const start = availModalEl.querySelector('#av_start').value;
                const end = availModalEl.querySelector('#av_end').value;
                const price = availModalEl.querySelector('#av_price').value;
                const checkin = availModalEl.querySelector('#av_checkin').value;
                const checkout = availModalEl.querySelector('#av_checkout').value;
                const errEl = availModalEl.querySelector('#av_error');

                const today = todayISO();
                if (!start) { errEl.textContent = 'Start date is required.'; errEl.style.display='block'; return; }
                if (!end) { errEl.textContent = 'End date is required.'; errEl.style.display='block'; return; }
                if (start < today) { errEl.textContent = 'Start date cannot be before today.'; errEl.style.display='block'; return; }
                if (end < today) { errEl.textContent = 'End date cannot be before today.'; errEl.style.display='block'; return; }
                if (end < start) { errEl.textContent = 'End date must be the same or after start date.'; errEl.style.display='block'; return; }
                const priceNum = parseFloat(price);
                if (isNaN(priceNum) || priceNum < 0) { errEl.textContent = 'Price per night must be a positive number.'; errEl.style.display='block'; return; }
                if (!checkin) { errEl.textContent = 'Check-in time required.'; errEl.style.display='block'; return; }
                if (!checkout) { errEl.textContent = 'Check-out time required.'; errEl.style.display='block'; return; }

                const editingIndexAttr = availModalEl.getAttribute('data-editing-index');
                const editingIndex = editingIndexAttr ? parseInt(editingIndexAttr) : -1;
                for (let i = 0; i < availabilities.length; i++) {
                    if (i === editingIndex) continue;
                    const other = availabilities[i];
                    if (!(end < other.start_date || start > other.end_date)) {
                        errEl.textContent = 'This availability overlaps an existing one.'; errEl.style.display='block'; return;
                    }
                }

                const entry = {
                    start_date: start,
                    end_date: end,
                    price_per_night: priceNum.toFixed(2),
                    check_in_time: checkin,
                    check_out_time: checkout
                };

                if (!isNaN(editingIndex) && editingIndex >= 0) {
                    if (availabilities[editingIndex] && availabilities[editingIndex].id) {
                        entry.id = availabilities[editingIndex].id;
                    }
                    availabilities[editingIndex] = entry;
                } else {
                    availabilities.push(entry);
                }

                saveToHiddenInput();
                renderList();
                const successEl = availModalEl.querySelector('#av_success');
                if (successEl) {
                    successEl.textContent = 'Availability saved.';
                    successEl.style.display = 'block';
                    setTimeout(function(){ successEl.style.display = 'none'; }, 3000);
                }
            });

                const avStartEl = availModalEl.querySelector('#av_start');
                const avEndEl = availModalEl.querySelector('#av_end');
                const avPriceEl = availModalEl.querySelector('#av_price');
                if (avStartEl) {
                    avStartEl.min = todayISO();
                    avStartEl.addEventListener('change', function(){ if (avStartEl.value && avEndEl) avEndEl.min = avStartEl.value; });
                }
                if (avEndEl) {
                    avEndEl.min = todayISO();
                    avEndEl.addEventListener('change', function(){ if (avEndEl.value && avStartEl) avStartEl.max = avEndEl.value; });
                }
                if (avPriceEl) {
                    avPriceEl.setAttribute('min','0');
                    avPriceEl.addEventListener('input', function(){ const v = parseFloat(avPriceEl.value); if (!isNaN(v) && v < 0) avPriceEl.value = '0'; });
                }

            try {
                const initial = {!! json_encode($availabilities->map(function($a){ return [
                    'id' => $a->id,
                    'start_date' => $a->start_date,
                    'end_date' => $a->end_date,
                    'price_per_night' => (float)$a->price_per_night,
                    'check_in_time' => $a->check_in_time,
                    'check_out_time' => $a->check_out_time
                ]; })->all()) !!};
                if (Array.isArray(initial)) { availabilities = initial; }
            } catch(e){ availabilities = []; }
            saveToHiddenInput();
            renderList(1);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initThumbs);
        } else {
            initThumbs();
        }
    })();
</script>
@endpush

@endsection