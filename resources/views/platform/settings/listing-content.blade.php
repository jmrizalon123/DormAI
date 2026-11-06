@extends('layouts.app')
@section('title', 'Listing page content')
@section('page-title', 'Platform settings')
@section('page-subtitle', 'Hero image, headlines, and marketplace copy')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel p-4">
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <div class="fw-semibold mb-1">Could not save</div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('platform.settings.listing-content.update') }}" enctype="multipart/form-data" data-da-no-confirm>
                    @csrf

                    <h2 class="h6 mb-3">Marketplace header (logo)</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="brandTitlePrimary">Brand title — first part</label>
                            <input type="text" name="brand_title_primary" id="brandTitlePrimary" class="form-control @error('brand_title_primary') is-invalid @enderror" value="{{ old('brand_title_primary', $content['brand_title_primary']) }}" required maxlength="40">
                            @error('brand_title_primary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Shown in the marketplace header in the main brand color (e.g. <strong>Dorm</strong>).</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="brandTitleAccent">Brand title — accent part</label>
                            <input type="text" name="brand_title_accent" id="brandTitleAccent" class="form-control @error('brand_title_accent') is-invalid @enderror" value="{{ old('brand_title_accent', $content['brand_title_accent']) }}" required maxlength="40">
                            @error('brand_title_accent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Second word in the accent color (e.g. <strong>AI</strong>).</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="brandTagline">Tagline under the logo</label>
                            <input type="text" name="brand_tagline" id="brandTagline" class="form-control @error('brand_tagline') is-invalid @enderror" value="{{ old('brand_tagline', $content['brand_tagline']) }}" maxlength="120">
                            @error('brand_tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Short line under the logo on desktop; hidden on very small screens. Leave blank to hide.</div>
                        </div>
                    </div>

                    <h2 class="h6 mb-3">Hero section</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label" for="heroTitle">Headline</label>
                            <input type="text" name="hero_title" id="heroTitle" class="form-control @error('hero_title') is-invalid @enderror" value="{{ old('hero_title', $content['hero_title']) }}" required maxlength="200">
                            @error('hero_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="heroLead">Subheadline</label>
                            <textarea name="hero_lead" id="heroLead" rows="3" class="form-control @error('hero_lead') is-invalid @enderror" required maxlength="500">{{ old('hero_lead', $content['hero_lead']) }}</textarea>
                            @error('hero_lead')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="heroImages">Add hero slides</label>
                            <input type="file" name="hero_images[]" id="heroImages" class="form-control @error('hero_images') is-invalid @enderror @error('hero_images.*') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" multiple>
                            @error('hero_images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('hero_images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Select multiple files (max {{ \App\Support\Platform\PlatformListingContentSettings::MAX_HERO_IMAGES }} slides). New uploads are appended when you save.</div>
                            <div id="heroImagesPreview" class="row g-2 mt-2 d-none" aria-live="polite"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="heroImageUrl">Or add one image URL</label>
                            <input type="url" name="hero_image_url" id="heroImageUrl" class="form-control @error('hero_image_url') is-invalid @enderror" value="{{ old('hero_image_url') }}" placeholder="https://…">
                            @error('hero_image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h2 class="h6 mb-3">Search card</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="searchTitle">Title</label>
                            <input type="text" name="search_title" id="searchTitle" class="form-control @error('search_title') is-invalid @enderror" value="{{ old('search_title', $content['search_title']) }}" required maxlength="80">
                            @error('search_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="searchSubtitle">Subtitle</label>
                            <input type="text" name="search_subtitle" id="searchSubtitle" class="form-control" value="{{ old('search_subtitle', $content['search_subtitle']) }}" maxlength="120">
                        </div>
                    </div>

                    <h2 class="h6 mb-3">Listings grid</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label" for="listingsHeading">Section heading</label>
                            <input type="text" name="listings_heading" id="listingsHeading" class="form-control @error('listings_heading') is-invalid @enderror" value="{{ old('listings_heading', $content['listings_heading']) }}" required maxlength="80">
                            @error('listings_heading')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="listingsEmptyHint">Empty catalog hint</label>
                            <input type="text" name="listings_empty_hint" id="listingsEmptyHint" class="form-control" value="{{ old('listings_empty_hint', $content['listings_empty_hint']) }}" maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="listingsNoMatchHint">No filter match hint</label>
                            <input type="text" name="listings_no_match_hint" id="listingsNoMatchHint" class="form-control" value="{{ old('listings_no_match_hint', $content['listings_no_match_hint']) }}" maxlength="200">
                        </div>
                    </div>

                    <h2 class="h6 mb-3">Footer</h2>
                    <div class="mb-4">
                        <label class="form-label" for="footerBlurb">Marketplace footer text</label>
                        <textarea name="footer_blurb" id="footerBlurb" rows="3" class="form-control" maxlength="400">{{ old('footer_blurb', $content['footer_blurb']) }}</textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">Save listing content</button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">Preview marketplace</a>
                    </div>
                </form>

                @if(count($heroSlides) > 0)
                    <div class="mt-4 pt-4 border-top">
                        <div class="small fw-semibold mb-2">Current slideshow ({{ count($heroSlides) }})</div>
                        <div class="row g-2">
                            @foreach($heroSlides as $index => $slideUrl)
                                <div class="col-6 col-md-4">
                                    <div class="border rounded overflow-hidden position-relative">
                                        <div class="ratio ratio-16x9">
                                            <img src="{{ $slideUrl }}" alt="" class="object-fit-cover">
                                        </div>
                                        <form method="POST" action="{{ route('platform.settings.listing-content.hero-images.destroy', $index) }}" class="position-absolute top-0 end-0 m-1" data-da-confirm="Remove this hero slide?" data-da-confirm-title="Remove slide">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" aria-label="Remove slide {{ $index + 1 }}"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('platform.settings.listing-content.reset') }}"
                    class="mt-3"
                    data-da-confirm="Reset all listing page text and hero image to config defaults?"
                    data-da-confirm-title="Reset listing content"
                >
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reset to config default</button>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="da-panel p-4">
                <h2 class="h6 mb-2">Config fallback</h2>
                <p class="small text-muted mb-0">Values in <code>config/dormai.php</code> under <code>listing_content_defaults</code> apply when nothing is saved here or after reset.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if(file_exists(public_path('js/platform-listing-content.js')))
        <script defer src="{{ asset('js/platform-listing-content.js') }}?v={{ filemtime(public_path('js/platform-listing-content.js')) }}"></script>
    @endif
@endpush
