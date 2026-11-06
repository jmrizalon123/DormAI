<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name').' — Find dormitories & boarding houses')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('layouts.partials.theme-assets')
    <script>
        try {
            if (localStorage.getItem('dormai.theme.v1') === 'dark') {
                document.documentElement.setAttribute('data-da-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        } catch (e) {}
    </script>
    @stack('styles')
</head>
<body class="da-marketplace d-flex flex-column min-vh-100">
    @php($marketplaceOverHero = View::hasSection('hero'))
    @if($marketplaceOverHero)
        <div class="da-marketplace-hero-shell">
    @endif
    @include('layouts.partials.marketplace-header', ['overHero' => $marketplaceOverHero])

    @yield('hero')

    @if($marketplaceOverHero)
        </div>
    @endif

    <main class="da-marketplace-main flex-grow-1">
        @include('components.flash-messages')
        @yield('content')
    </main>

    <footer class="da-marketplace-footer mt-auto">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="mb-3">
                        <x-brand.dormai-logo
                            variant="full"
                            surface="light"
                            class="da-marketplace-logo da-marketplace-logo--footer"
                            :brand-primary="$marketplaceListingContent['brand_title_primary']"
                            :brand-accent="$marketplaceListingContent['brand_title_accent']"
                        />
                    </div>
                    @php($marketplaceFooterBlurb = \App\Support\Platform\PlatformListingContentSettings::defaults()['footer_blurb'])
                    <p class="text-muted small mb-0 pe-md-4">{{ $marketplaceFooterBlurb }}</p>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small fw-semibold mb-2">Explore</div>
                    <ul class="list-unstyled small mb-0 da-marketplace-footer-links">
                        <li><a href="{{ route('home') }}">All listings</a></li>
                        <li><a href="{{ route('public.contact.create') }}">Contact us</a></li>
                        <li><a href="{{ route('public.contact.track') }}">Track a message</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-4">
                    <div class="small fw-semibold mb-2">For owners</div>
                    <ul class="list-unstyled small mb-0 da-marketplace-footer-links">
                        <li><a href="{{ route('register') }}">Register organization</a></li>
                        <li><a href="{{ route('login') }}">Staff sign in</a></li>
                    </ul>
                </div>
            </div>
            <div class="da-marketplace-footer-bottom small text-muted pt-4 mt-4">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Built for dorm &amp; boarding house operators in the Philippines.
            </div>
        </div>
    </footer>

    @include('components.da-confirm-modal')
    <div id="daToastHost" class="da-toast-host" aria-live="polite" aria-atomic="true"></div>

    @if(! empty($listingAssistantEnabled) && ! request()->routeIs('public.listings.show'))
        <x-listing-assistant-widget
            :endpoint="route('public.marketplace.assistant')"
            widget-id="daMarketplaceAssistant"
            title="DormAI assistant"
            :subtitle="! empty($listingAiCompanionEnabled)
                ? 'Search companion — combines live listing data with optional AI help for budgeting and next steps. Specific listings and prices always come from the database.'
                : 'Local helper — answers from our listings database (no external AI). Open a property for full details.'"
            fab-label="Ask DormAI"
            intro="Hi — I read public listings from the database. Ask for available properties, search tips, or open a listing for price and address."
            placeholder="e.g. How do I filter by city? Where can I see monthly rates?"
            aria-label="DormAI marketplace assistant panel"
            quick-replies="Recommend listings under ₱10k|Show listings with available beds|How does search work?"
            :welcome-reply="isset($marketplaceAssistantWelcome) ? $marketplaceAssistantWelcome['reply'] : null"
            :welcome-cards="isset($marketplaceAssistantWelcome) ? $marketplaceAssistantWelcome['cards'] : null"
            :auto-open-welcome-once="request()->routeIs('home')"
        />
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('layouts.partials.dormai-feedback-scripts')
    @if(file_exists(public_path('js/marketplace-nav.js')))
        <script defer src="{{ asset('js/marketplace-nav.js') }}?v={{ filemtime(public_path('js/marketplace-nav.js')) }}"></script>
    @endif
    @if(! empty($listingAssistantEnabled) && file_exists(public_path('js/listing-assistant-widget.js')))
        <script defer src="{{ asset('js/listing-assistant-widget.js') }}?v={{ filemtime(public_path('js/listing-assistant-widget.js')) }}"></script>
    @endif
    @stack('scripts')
</body>
</html>
