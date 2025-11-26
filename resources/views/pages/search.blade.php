@extends('layouts.app')

@section('content')
<div class="container py-4">
    <form method="GET" action="/search">
    <div class="row g-4">
        <div class="col-12 col-md-3">
            <div class="card mb-4 p-3">
                <h6>Filters</h6>

                <div class="mb-3">
                    <label class="form-label">Order By</label>
                    <select name="order_by" class="form-select">
                        <option value="relevance" @if(($order_by ?? '') === 'relevance') selected @endif>Search closeness</option>
                        <option value="price" @if(($order_by ?? '') === 'price') selected @endif>Price</option>
                        <option value="rating" @if(($order_by ?? '') === 'rating') selected @endif>Rating</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Order Direction</label>
                    <select name="order_dir" class="form-select">
                        <option value="desc" @if(($order_dir ?? '') === 'desc') selected @endif>Descending</option>
                        <option value="asc" @if(($order_dir ?? '') === 'asc') selected @endif>Ascending</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating (min / max)</label>
                    <div class="d-flex gap-2">
                        <input type="number" step="0.1" min="0" max="5" name="min_rating" class="form-control" placeholder="Min" value="{{ old('min_rating', $min_rating ?? '') }}">
                        <input type="number" step="0.1" min="0" max="5" name="max_rating" class="form-control" placeholder="Max" value="{{ old('max_rating', $max_rating ?? '') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Minimum stay (nights)</label>
                    <input type="number" name="min_stay" class="form-control" min="1" value="{{ old('min_stay', $min_stay ?? '') }}">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-secondary">Apply</button>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-9">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-row flex-wrap gap-2 align-items-end">
                        <div class="form-group flex-fill" style="min-width:160px">
                            <label class="form-label">Where to</label>
                            <input type="text" name="where" class="form-control" placeholder="City, address or landmark" value="{{ old('where', $where ?? '') }}">
                        </div>

                        <div class="form-group" style="min-width:150px">
                            <label class="form-label">Check-In</label>
                            <input type="date" name="checkin" id="checkin" class="form-control" value="{{ old('checkin', $checkin ?? '') }}">
                        </div>

                        <div class="form-group" style="min-width:150px">
                            <label class="form-label">Check-Out</label>
                            <input type="date" name="checkout" id="checkout" class="form-control" value="{{ old('checkout', $checkout ?? '') }}">
                        </div>

                        <div class="form-group" style="min-width:160px">
                            <label class="form-label">Price</label>
                            <select name="price" class="form-select">
                                <option value="" @if(empty($price)) selected @endif>Any</option>
                                <option value="0" @if(($price ?? '') === '0') selected @endif>Up to $50</option>
                                <option value="50" @if(($price ?? '') === '50') selected @endif>$50 - $100</option>
                                <option value="100" @if(($price ?? '') === '100') selected @endif>$100 - $200</option>
                                <option value="200" @if(($price ?? '') === '200') selected @endif>$200 - $500</option>
                                <option value="500" @if(($price ?? '') === '500') selected @endif>$500+</option>
                            </select>
                        </div>

                        <div class="form-group" style="min-width:120px">
                            <label class="form-label">Guests</label>
                            <input type="number" name="guests" class="form-control" min="1" value="{{ old('guests', $guests ?? 1) }}">
                        </div>

                        <div class="form-group ms-auto">
                            <button type="submit" class="btn btn-primary btn-lg">Search</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="search-results-container">
                @include('pages._search_results', ['listings' => $listings])
            </div>
        </div>
    </div>
    </form>
</div>

@push('scripts')
<script>
    (function(){
        function localISODate(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        }
        function parseLocalDate(s) {
            if (!s) return null;
            const parts = s.split('-');
            if (parts.length !== 3) return null;
            return new Date(parts[0], parseInt(parts[1],10)-1, parts[2]);
        }
        function addDaysToLocal(dateStr, days) {
            const d = parseLocalDate(dateStr);
            if (!d) return null;
            d.setDate(d.getDate() + days);
            return localISODate(d);
        }

        const todayStr = localISODate(new Date());
        const form = document.querySelector('form[action="/search"]');
        const checkin = document.getElementById('checkin');
        const checkout = document.getElementById('checkout');
        const resultsContainer = document.getElementById('search-results-container');

        if (checkin) checkin.min = todayStr;
        if (checkout) checkout.min = todayStr;
        if (checkin && checkin.value) {
            const next = addDaysToLocal(checkin.value, 1) || todayStr;
            if (checkout) checkout.min = next;
        }
        if (checkout && checkout.value) {
            const prev = addDaysToLocal(checkout.value, -1);
            if (checkin && prev) checkin.max = prev;
        }

        function onCheckinChange() {
            if (!checkin) return;
            if (checkin.value) {
                const nextDay = addDaysToLocal(checkin.value, 1) || todayStr;
                if (checkout) checkout.min = nextDay;
                if (checkout && checkout.value) {
                    const ci = parseLocalDate(checkin.value);
                    const co = parseLocalDate(checkout.value);
                    if (ci && co && ci >= co) checkout.value = '';
                }
            } else {
                if (checkout) checkout.min = todayStr;
            }
        }
        function onCheckoutChange() {
            if (!checkout) return;
            if (checkout.value) {
                const prevDay = addDaysToLocal(checkout.value, -1);
                if (checkin && prevDay) checkin.max = prevDay;
                if (checkin && checkin.value) {
                    const ci = parseLocalDate(checkin.value);
                    const co = parseLocalDate(checkout.value);
                    if (ci && co && ci >= co) checkin.value = '';
                }
            } else {
                if (checkin) checkin.max = '';
            }
        }

        if (checkin) checkin.addEventListener('change', onCheckinChange);
        if (checkout) checkout.addEventListener('change', onCheckoutChange);

        function buildQueryParams() {
            const params = new URLSearchParams();
            const formData = new FormData(form);
            for (const [k, v] of formData.entries()) {
                if (v === null || v === undefined) continue;
                const s = String(v).trim();
                if (s === '') continue;
                params.append(k, s);
            }
            return params.toString();
        }

        function validateBeforeSubmit() {
            const ci = parseLocalDate(checkin ? checkin.value : '');
            const co = parseLocalDate(checkout ? checkout.value : '');
            if (ci && ci < parseLocalDate(todayStr)) { alert('Check-in cannot be before today.'); return false; }
            if (co && co < parseLocalDate(todayStr)) { alert('Check-out cannot be before today.'); return false; }
            if (ci && co && ci >= co) { alert('Check-in must be before check-out.'); return false; }

            const minRatingEl = form.querySelector('input[name="min_rating"]');
            const maxRatingEl = form.querySelector('input[name="max_rating"]');
            if (minRatingEl && maxRatingEl) {
                const minR = minRatingEl.value ? parseFloat(minRatingEl.value) : null;
                const maxR = maxRatingEl.value ? parseFloat(maxRatingEl.value) : null;
                if (minR !== null && maxR !== null && minR > maxR) { alert('Minimum rating cannot be greater than maximum rating.'); return false; }
            }

            return true;
        }

        async function performAjaxSearch(pushHistory = true) {
            if (!form) return;
            if (!validateBeforeSubmit()) return;
            const qs = buildQueryParams();
            const url = form.action + (qs ? ('?' + qs) : '');
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Network response was not ok');
                const html = await res.text();
                if (resultsContainer) resultsContainer.innerHTML = html;
                if (pushHistory && window.history && window.history.pushState) {
                    window.history.pushState({}, '', url);
                }
            } catch (err) {
                console.error('Search fetch failed', err);
                alert('Search failed. Please try again.');
            }
        }

        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                performAjaxSearch(true);
            });
        }

        window.addEventListener('popstate', function(){
            performAjaxSearch(false);
        });
    })();
</script>
@endpush

@endsection
