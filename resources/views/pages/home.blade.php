@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/search" class="d-flex flex-row flex-wrap gap-2 align-items-end">
                <div class="form-group flex-fill" style="min-width:160px">
                    <label class="form-label">Where to</label>
                    <input type="text" name="where" class="form-control" placeholder="City, address or landmark">
                </div>

                <div class="form-group" style="min-width:150px">
                    <label class="form-label">Check-In</label>
                    <input type="date" name="checkin" id="checkin" class="form-control">
                </div>

                <div class="form-group" style="min-width:150px">
                    <label class="form-label">Check-Out</label>
                    <input type="date" name="checkout" id="checkout" class="form-control">
                </div>

                <div class="form-group" style="min-width:160px">
                    <label class="form-label">Price</label>
                    <select name="price" class="form-select">
                        <option value="">Any</option>
                        <option value="0">Up to $50</option>
                        <option value="50">$50 - $100</option>
                        <option value="100">$100 - $200</option>
                        <option value="200">$200 - $500</option>
                        <option value="500">$500+</option>
                    </select>
                </div>

                <div class="form-group" style="min-width:120px">
                    <label class="form-label">Guests</label>
                    <input type="number" name="guests" class="form-control" min="1" value="1">
                </div>

                <div class="form-group ms-auto">
                    <button type="submit" class="btn btn-primary btn-lg">Search</button>
                </div>
            </form>
        </div>
    </div>

    @include('partials.cards', ['listings' => $listings])
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
        const whereInput = form ? form.querySelector('input[name="where"]') : null;
        const priceSelect = form ? form.querySelector('select[name="price"]') : null;
        const guestsInput = form ? form.querySelector('input[name="guests"]') : null;

        const checkin = document.getElementById('checkin');
        const checkout = document.getElementById('checkout');

        if (checkin) checkin.min = todayStr;
        if (checkout) checkout.min = todayStr;

        function onCheckinChange() {
            if (!checkin) return;
            if (checkin.value) {
                const nextDay = addDaysToLocal(checkin.value, 1);
                if (checkout && nextDay) checkout.min = nextDay;
                if (checkout && checkout.value) {
                    const ci = parseLocalDate(checkin.value);
                    const co = parseLocalDate(checkout.value);
                    if (ci && co && ci >= co) checkout.value = '';
                }
                checkin.max = '';
            } else {
                if (checkout) checkout.min = todayStr;
                checkin.max = '';
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

        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();

                const whereVal = whereInput ? whereInput.value.trim() : '';

                const ci = parseLocalDate(checkin ? checkin.value : '');
                const co = parseLocalDate(checkout ? checkout.value : '');
                const today = parseLocalDate(todayStr);

                if (ci && ci < today) { alert('Check-in cannot be before today.'); return; }
                if (co && co < today) { alert('Check-out cannot be before today.'); return; }
                if (ci && co && ci >= co) { alert('Check-in must be before check-out.'); return; }

                const params = new URLSearchParams();
                if (whereVal) params.set('where', whereVal);
                if (checkin && checkin.value) params.set('checkin', checkin.value);
                if (checkout && checkout.value) params.set('checkout', checkout.value);
                if (priceSelect && priceSelect.value) params.set('price', priceSelect.value);
                if (guestsInput && guestsInput.value) params.set('guests', guestsInput.value);

                const target = form.action + (params.toString() ? ('?' + params.toString()) : '');
                window.location.href = target;
            });
        }
    })();
</script>
@endpush

@endsection