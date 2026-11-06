<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('layouts.partials.theme-assets')
</head>
<body class="da-guest">
    <div class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5">
                    <div class="text-center mb-4">
                        <a href="{{ route('home') }}" class="da-auth-brand-link text-decoration-none d-inline-block" aria-label="{{ config('app.name') }} home">
                            <x-brand.dormai-logo variant="full" surface="light" class="da-auth-brand-logo" />
                        </a>
                        <p class="da-auth-tagline mt-2 mb-0">Property & dormitory management platform</p>
                    </div>
                    @include('components.flash-messages')
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @include('components.da-confirm-modal')
    <div id="daToastHost" class="da-toast-host" aria-live="polite" aria-atomic="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('layouts.partials.dormai-feedback-scripts')
</body>
</html>
