@php
    $isEdit = isset($property);
    $listing = old('listing')
        ? array_merge(\App\Support\Property\PropertyListingSettings::defaults(), (array) old('listing'))
        : ($isEdit
            ? \App\Support\Property\PropertyListingSettings::forProperty($property)
            : \App\Support\Property\PropertyListingSettings::defaults());
    $amenityPresets = config('dormai.property_amenity_presets', []);
    $selectedAmenities = old('listing.amenities', $listing['amenities'] ?? []);
    $highlightsText = old('listing.highlights', \App\Support\Property\PropertyListingSettings::highlightsText($listing));
    $customAmenitiesText = old('listing.custom_amenities', \App\Support\Property\PropertyListingSettings::customAmenitiesText($listing));
    $mapDefaults = \App\Support\Organization\OrganizationMapSettings::forOrganization(auth()->user()?->organization);
    $propertyLat = old('latitude', $property->latitude ?? '');
    $propertyLng = old('longitude', $property->longitude ?? '');
@endphp

<div class="da-panel da-property-form">
    <form method="POST" action="{{ $action }}" class="da-property-form-inner" enctype="multipart/form-data">
        @csrf
        @if ($method ?? false)
            @method($method)
        @endif

        <div class="da-property-form-nav px-3 px-lg-4 pt-3 pb-2">
            <ul class="nav da-form-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-basics" data-bs-toggle="tab" data-bs-target="#pane-basics" type="button" role="tab">Basics</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-location" data-bs-toggle="tab" data-bs-target="#pane-location" type="button" role="tab">Location & contact</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-amenities" data-bs-toggle="tab" data-bs-target="#pane-amenities" type="button" role="tab">Amenities</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-appeal" data-bs-toggle="tab" data-bs-target="#pane-appeal" type="button" role="tab">Tenant appeal</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-photos" data-bs-toggle="tab" data-bs-target="#pane-photos" type="button" role="tab">Photos</button>
                </li>
            </ul>
        </div>

        <div class="tab-content da-form-tab-panes p-3 p-lg-4">
            <div class="tab-pane fade show active" id="pane-basics" role="tabpanel" tabindex="0">
                <p class="text-muted small mb-4">Core identity and status — what tenants see first in listings.</p>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Property name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $property->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $property->code ?? '') }}" placeholder="e.g. CVD1">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Marketing tagline</label>
                        <input type="text" name="listing[tagline]" class="form-control @error('listing.tagline') is-invalid @enderror" value="{{ old('listing.tagline', $listing['tagline'] ?? '') }}" maxlength="160" placeholder="Walk to campus · Quiet study-friendly dorm">
                        <div class="form-text">Short hook for public stay pages (max 160 characters).</div>
                        @error('listing.tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach (App\Enums\PropertyStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $property?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch da-form-switch-card w-100">
                            <input type="hidden" name="listing[public_listing]" value="0">
                            <input class="form-check-input" type="checkbox" name="listing[public_listing]" id="listingPublic" value="1" @checked(old('listing.public_listing', $listing['public_listing'] ?? true))>
                            <label class="form-check-label" for="listingPublic">Show on public stay listings</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Full description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the vibe, who it is best for, and what makes your dorm different.">{{ old('description', $property->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade da-location-tab-pane" id="pane-location" role="tabpanel" tabindex="0">
                <div class="da-location-tab-layout row g-4 g-lg-0">
                    <div class="col-lg-5 da-location-tab-fields">
                        <p class="da-location-tab-lead text-muted small mb-4 mb-lg-3">
                            Set the mailing address and contact details on the left. Pin the exact spot on the map — we’ll fill in address fields when you drop the pin.
                        </p>

                        <section class="da-form-section" aria-labelledby="location-section-address">
                            <h2 class="da-form-section-title" id="location-section-address">
                                <i class="bi bi-mailbox2" aria-hidden="true"></i>
                                Postal address
                            </h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="propertyAddressLine1">Street address</label>
                                    <input type="text" name="address_line1" id="propertyAddressLine1" class="form-control" value="{{ old('address_line1', $property->address_line1 ?? '') }}" autocomplete="street-address">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="propertyAddressLine2">Address line 2</label>
                                    <input type="text" name="address_line2" id="propertyAddressLine2" class="form-control" value="{{ old('address_line2', $property->address_line2 ?? '') }}" placeholder="Building, barangay, landmark" autocomplete="address-line2">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="propertyCity">City</label>
                                    <input type="text" name="city" id="propertyCity" class="form-control" value="{{ old('city', $property->city ?? '') }}" autocomplete="address-level2">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="propertyProvince">Province</label>
                                    <input type="text" name="province" id="propertyProvince" class="form-control" value="{{ old('province', $property->province ?? '') }}" autocomplete="address-level1">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="propertyPostalCode">Postal code</label>
                                    <input type="text" name="postal_code" id="propertyPostalCode" class="form-control" value="{{ old('postal_code', $property->postal_code ?? '') }}" autocomplete="postal-code">
                                </div>
                            </div>
                        </section>

                        <section class="da-form-section" aria-labelledby="location-section-contact">
                            <h2 class="da-form-section-title" id="location-section-contact">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                Contact
                            </h2>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label" for="propertyEmail">Contact email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="propertyEmail" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $property->email ?? '') }}" autocomplete="email" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="propertyPhone">Contact phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="propertyPhone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $property->phone ?? '') }}" autocomplete="tel" required>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="da-form-section mb-0" aria-labelledby="location-section-landmarks">
                            <h2 class="da-form-section-title" id="location-section-landmarks">
                                <i class="bi bi-signpost-split" aria-hidden="true"></i>
                                Getting here
                            </h2>
                            <label class="form-label" for="propertyNearbyLandmarks">Nearby landmarks & commute</label>
                            <textarea name="listing[nearby_landmarks]" id="propertyNearbyLandmarks" class="form-control" rows="3" placeholder="5 min walk to campus&#10;Jeepney stop in front">{{ old('listing.nearby_landmarks', $listing['nearby_landmarks'] ?? '') }}</textarea>
                            <div class="form-text">Shown on listings to help tenants picture the commute.</div>
                        </section>
                    </div>

                    <div class="col-lg-7 da-location-tab-map">
                        <div class="da-location-map-panel" aria-labelledby="location-map-panel-title">
                            <div class="da-location-map-panel__header">
                                <div class="da-location-map-panel__title-wrap">
                                    <h2 class="da-location-map-panel__title mb-1" id="location-map-panel-title">Exact location</h2>
                                    <span
                                        class="da-location-map-panel__coords @if(filled($propertyLat) && filled($propertyLng)) is-set @endif"
                                        id="propertyMapCoords"
                                        aria-live="polite"
                                    >
                                        @if(filled($propertyLat) && filled($propertyLng))
                                            {{ number_format((float) $propertyLat, 5) }}, {{ number_format((float) $propertyLng, 5) }}
                                        @else
                                            Drop a pin on the map
                                        @endif
                                    </span>
                                </div>
                                <div class="da-location-map-panel__actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="propertyMapGeocode">
                                        <i class="bi bi-search me-1" aria-hidden="true"></i>
                                        Find from address
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link text-muted" id="propertyMapClear">Clear pin</button>
                                </div>
                            </div>
                            <div class="da-map-shell da-location-map-panel__map">
                                <div
                                    id="propertyMapPicker"
                                    class="da-property-map-picker"
                                    data-da-map-picker
                                    data-lat-input="propertyLatitude"
                                    data-lng-input="propertyLongitude"
                                    data-coords-label="propertyMapCoords"
                                    data-geocode-button="propertyMapGeocode"
                                    data-clear-button="propertyMapClear"
                                    data-tab-target="#pane-location"
                                    data-empty-label="Drop a pin on the map"
                                    data-default-lat="{{ $mapDefaults['latitude'] }}"
                                    data-default-lng="{{ $mapDefaults['longitude'] }}"
                                    data-default-zoom="{{ $mapDefaults['zoom'] }}"
                                    data-geocode-url="{{ route('organization.properties.geocode') }}"
                                    data-reverse-geocode-url="{{ route('organization.properties.reverse-geocode') }}"
                                ></div>
                            </div>
                            <p class="da-location-map-panel__hint small text-muted mb-0">
                                Click or drag the pin — address fields on the left update automatically.
                            </p>
                            <input type="hidden" name="latitude" id="propertyLatitude" value="{{ $propertyLat }}">
                            <input type="hidden" name="longitude" id="propertyLongitude" value="{{ $propertyLng }}">
                            @error('latitude')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            @error('longitude')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-amenities" role="tabpanel" tabindex="0">
                <p class="text-muted small mb-3">Select amenities that matter to students and young professionals.</p>
                <div class="row g-2 mb-4 da-amenity-grid">
                    @foreach ($amenityPresets as $preset)
                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="da-amenity-chip w-100">
                                <input type="checkbox" name="listing[amenities][]" value="{{ $preset['key'] }}" @checked(in_array($preset['key'], $selectedAmenities, true))>
                                <span class="da-amenity-chip-inner">
                                    <i class="bi {{ $preset['icon'] }}"></i>
                                    <span>{{ $preset['label'] }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('listing.amenities.*')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch da-form-switch-card h-100">
                            <input type="hidden" name="listing[wifi_included]" value="0">
                            <input class="form-check-input" type="checkbox" name="listing[wifi_included]" id="wifiIncluded" value="1" @checked(old('listing.wifi_included', $listing['wifi_included'] ?? true))>
                            <label class="form-check-label" for="wifiIncluded">Wi‑Fi included in rent</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch da-form-switch-card h-100">
                            <input type="hidden" name="listing[utilities_included]" value="0">
                            <input class="form-check-input" type="checkbox" name="listing[utilities_included]" id="utilitiesIncluded" value="1" @checked(old('listing.utilities_included', $listing['utilities_included'] ?? false))>
                            <label class="form-check-label" for="utilitiesIncluded">Utilities included</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Other amenities <span class="text-muted fw-normal">(one per line)</span></label>
                        <textarea name="listing[custom_amenities]" class="form-control" rows="3" placeholder="Monthly pest control&#10;Biometric entrance">{{ $customAmenitiesText }}</textarea>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-appeal" role="tabpanel" tabindex="0">
                <p class="text-muted small mb-4">Pricing signals, policies, and selling points that convert lookers into applicants.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Starting rate (₱ / month)</label>
                        <input type="number" name="listing[starting_rate]" class="form-control" min="0" step="0.01" value="{{ old('listing.starting_rate', $listing['starting_rate'] ?? '') }}" placeholder="3500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum stay (months)</label>
                        <input type="number" name="listing[min_stay_months]" class="form-control" min="1" max="60" value="{{ old('listing.min_stay_months', $listing['min_stay_months'] ?? '') }}" placeholder="3">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender policy</label>
                        <select name="listing[gender_policy]" class="form-select">
                            <option value="mixed" @selected(old('listing.gender_policy', $listing['gender_policy'] ?? 'mixed') === 'mixed')>Mixed</option>
                            <option value="female_only" @selected(old('listing.gender_policy', $listing['gender_policy'] ?? '') === 'female_only')>Female only</option>
                            <option value="male_only" @selected(old('listing.gender_policy', $listing['gender_policy'] ?? '') === 'male_only')>Male only</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-in time</label>
                        <input type="time" name="listing[check_in_time]" class="form-control" value="{{ old('listing.check_in_time', $listing['check_in_time'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-out time</label>
                        <input type="time" name="listing[check_out_time]" class="form-control" value="{{ old('listing.check_out_time', $listing['check_out_time'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Highlights <span class="text-muted fw-normal">(one selling point per line, max 8)</span></label>
                        <textarea name="listing[highlights]" class="form-control" rows="4" placeholder="Fiber internet in every room&#10;Dedicated study tables&#10;No curfew on weekends">{{ $highlightsText }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">House rules summary</label>
                        <textarea name="listing[house_rules]" class="form-control" rows="4" placeholder="Visitors until 9 PM&#10;Quiet hours 10 PM – 6 AM">{{ old('listing.house_rules', $listing['house_rules'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-photos" role="tabpanel" tabindex="0">
                <p class="text-muted small mb-3">Strong photos increase reservation requests — add exteriors, rooms, and common areas.</p>

                @if($isEdit)
                    @php($existingImages = $property->images ?? collect())
                    @if($existingImages->isNotEmpty())
                        <div class="row g-2 mb-4 da-property-edit-gallery">
                            @foreach($existingImages as $image)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="da-image-upload-thumb border rounded overflow-hidden position-relative">
                                        <img src="{{ $image->url() }}" alt="" class="w-100 h-100 object-fit-cover">
                                        @if($image->is_primary)
                                            <span class="badge text-bg-dark position-absolute top-0 start-0 m-2">Cover</span>
                                        @endif
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 da-property-image-remove"
                                            data-url="{{ route('organization.properties.images.destroy', [$property, $image]) }}"
                                            aria-label="Remove image"
                                        >
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-3">No photos yet — upload below.</p>
                    @endif
                @endif

                <label class="form-label" for="propertyImagesInput">{{ $isEdit ? 'Add photos' : 'Photos' }}</label>
                <input
                    type="file"
                    name="images[]"
                    id="propertyImagesInput"
                    class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    multiple
                >
                <div class="form-text">Up to 12 images · 5 MB each{{ $isEdit ? ' · saved when you click Save changes' : ' · first image is the cover' }}.</div>
                @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div id="propertyImagesPreview" class="da-image-upload-preview row g-2 mt-3 d-none"></div>
            </div>
        </div>

        <div class="da-form-tab-footer d-flex flex-wrap gap-2 justify-content-between align-items-center px-3 px-lg-4 py-3 border-top">
            <span class="text-muted small">Fields on every tab are saved together when you submit.</span>
            <div class="d-flex gap-2">
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save changes' : 'Create property' }}</button>
            </div>
        </div>
    </form>
</div>

@include('layouts.partials.leaflet-assets')

@push('scripts')
    <script defer src="{{ asset('js/property-map-picker.js') }}@if(file_exists(public_path('js/property-map-picker.js')))?v={{ filemtime(public_path('js/property-map-picker.js')) }}@endif"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function showPropertyFormTab(target) {
                var btn = document.querySelector('.da-form-tabs button[data-bs-target="' + target + '"]');
                if (btn) {
                    bootstrap.Tab.getOrCreateInstance(btn).show();
                }
            }

            @if ($errors->hasAny(['email', 'phone', 'address_line1', 'address_line2', 'city', 'province', 'postal_code', 'latitude', 'longitude']))
            showPropertyFormTab('#pane-location');
            @elseif ($errors->hasAny(['listing.amenities', 'listing.custom_amenities', 'listing.wifi_included', 'listing.utilities_included']))
            showPropertyFormTab('#pane-amenities');
            @elseif ($errors->hasAny(['listing.tagline', 'listing.starting_rate', 'listing.min_stay_months', 'listing.gender_policy', 'listing.highlights', 'listing.house_rules']))
            showPropertyFormTab('#pane-appeal');
            @elseif ($errors->hasAny(['images', 'images.*']))
            showPropertyFormTab('#pane-photos');
            @endif

            var hash = window.location.hash;
            if (hash && document.querySelector('.da-form-tabs button[data-bs-target="' + hash + '"]')) {
                showPropertyFormTab(hash);
            }

            document.querySelectorAll('.da-form-tabs button[data-bs-toggle="tab"]').forEach(function (btn) {
                btn.addEventListener('shown.bs.tab', function () {
                    history.replaceState(null, '', btn.getAttribute('data-bs-target'));
                });
            });

            var input = document.getElementById('propertyImagesInput');
            var preview = document.getElementById('propertyImagesPreview');
            if (input && preview) {
                input.addEventListener('change', function () {
                    preview.innerHTML = '';
                    var files = Array.from(input.files || []);
                    if (!files.length) {
                        preview.classList.add('d-none');
                        return;
                    }
                    preview.classList.remove('d-none');
                    files.slice(0, 12).forEach(function (file, index) {
                        var col = document.createElement('div');
                        col.className = 'col-6 col-md-4 col-lg-3';
                        var card = document.createElement('div');
                        card.className = 'da-image-upload-thumb border rounded overflow-hidden position-relative';
                        var img = document.createElement('img');
                        img.className = 'w-100 h-100 object-fit-cover';
                        img.alt = file.name;
                        img.src = URL.createObjectURL(file);
                        var badge = document.createElement('span');
                        badge.className = 'badge text-bg-dark position-absolute top-0 start-0 m-2';
                        badge.textContent = index === 0 ? 'Cover' : String(index + 1);
                        card.appendChild(img);
                        card.appendChild(badge);
                        col.appendChild(card);
                        preview.appendChild(col);
                    });
                });
            }

            document.querySelectorAll('.da-property-image-remove').forEach(function (button) {
                button.addEventListener('click', function () {
                    var runDelete = function () {
                        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        var url = button.getAttribute('data-url');
                        if (!token || !url) {
                            return;
                        }

                        button.disabled = true;

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: '_method=DELETE',
                            credentials: 'same-origin',
                        })
                            .then(function (res) {
                                if (!res.ok) {
                                    throw new Error('Delete failed');
                                }
                                button.closest('.col-6')?.remove();
                                if (window.DormaiToast) {
                                    DormaiToast.success('Photo removed.');
                                }
                            })
                            .catch(function () {
                                button.disabled = false;
                                if (window.DormaiToast) {
                                    DormaiToast.danger('Could not remove the photo. Try again.');
                                }
                            });
                    };

                    if (window.DormaiConfirm) {
                        DormaiConfirm.ask({
                            title: 'Remove photo',
                            message: 'Remove this photo from the property gallery?',
                            destructive: true,
                            confirmLabel: 'Remove photo',
                        }).then(function (confirmed) {
                            if (confirmed) {
                                runDelete();
                            }
                        });
                        return;
                    }

                    if (window.confirm('Remove this photo?')) {
                        runDelete();
                    }
                });
            });
        });
    </script>
@endpush
