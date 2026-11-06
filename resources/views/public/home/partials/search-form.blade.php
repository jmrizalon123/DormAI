@php
    $searchUid = $searchUid ?? 'main';
    $showSearchHead = $showSearchHead ?? true;
    $searchFormClass = trim('da-marketplace-search ' . ($searchFormClass ?? ''));
@endphp
<form method="GET" action="{{ route('home') }}" class="{{ $searchFormClass }}" data-da-no-confirm id="da-search-form-{{ $searchUid }}">
    @if($showSearchHead)
        <div class="da-marketplace-search__head">
            <span class="da-marketplace-search__head-icon" aria-hidden="true"><i class="bi bi-sliders2"></i></span>
            <div>
                <span class="da-marketplace-search__head-title">{{ $listingContent['search_title'] }}</span>
                @if(filled($listingContent['search_subtitle']))
                    <span class="da-marketplace-search__head-sub">{{ $listingContent['search_subtitle'] }}</span>
                @endif
            </div>
        </div>
    @endif
    <div class="da-marketplace-search__body">
        <div class="da-marketplace-field">
            <label class="da-marketplace-field__label" for="search-q-{{ $searchUid }}">Keyword or area</label>
            <div class="da-marketplace-input-wrap">
                <i class="bi bi-geo-alt da-marketplace-input-wrap__icon" aria-hidden="true"></i>
                <input type="search" id="search-q-{{ $searchUid }}" name="q" value="{{ request('q') }}" class="form-control da-marketplace-input da-marketplace-input--icon" placeholder="Catbalogan, dorm name, near campus…" autocomplete="off">
            </div>
        </div>
        <div class="da-marketplace-field">
            <label class="da-marketplace-field__label" for="search-city-{{ $searchUid }}">City</label>
            <div class="da-marketplace-input-wrap">
                <i class="bi bi-buildings da-marketplace-input-wrap__icon" aria-hidden="true"></i>
                <select id="search-city-{{ $searchUid }}" name="city" class="form-select da-marketplace-input da-marketplace-input--icon">
                    <option value="">All cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div
            class="da-rate-range"
            data-da-rate-range
            data-floor="{{ $rateBounds['floor'] }}"
            data-ceil="{{ $rateBounds['ceil'] }}"
            data-step="{{ $rateBounds['step'] }}"
        >
            <div class="da-rate-range__header">
                <label class="da-marketplace-field__label mb-0" id="search-rate-label-{{ $searchUid }}">Monthly budget</label>
                <span class="da-rate-range__values" data-da-rate-range-label aria-live="polite">Any – Any</span>
            </div>
            <div class="da-rate-range__track" aria-labelledby="search-rate-label-{{ $searchUid }}">
                <div class="da-rate-range__rail" aria-hidden="true"></div>
                <div class="da-rate-range__fill" aria-hidden="true"></div>
                <input
                    type="range"
                    class="da-rate-range__input da-rate-range__input--min"
                    min="{{ $rateBounds['floor'] }}"
                    max="{{ $rateBounds['ceil'] }}"
                    step="{{ $rateBounds['step'] }}"
                    value="{{ max($rateBounds['floor'], min($rateMin, $rateMax - $rateBounds['step'])) }}"
                    aria-label="Minimum monthly rate"
                >
                <input
                    type="range"
                    class="da-rate-range__input da-rate-range__input--max"
                    min="{{ $rateBounds['floor'] }}"
                    max="{{ $rateBounds['ceil'] }}"
                    step="{{ $rateBounds['step'] }}"
                    value="{{ min($rateBounds['ceil'], max($rateMax, $rateMin + $rateBounds['step'])) }}"
                    aria-label="Maximum monthly rate"
                >
            </div>
            <div class="da-rate-range__ticks" aria-hidden="true">
                <span>₱0</span>
                <span>₱7.5k</span>
                <span>₱15k+</span>
            </div>
            <input type="hidden" name="min_rate" value="{{ request('min_rate') }}">
            <input type="hidden" name="max_rate" value="{{ request('max_rate') }}">
        </div>
        <button type="submit" class="btn btn-primary w-100 da-marketplace-search__submit">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span>Search properties</span>
        </button>
    </div>
</form>
