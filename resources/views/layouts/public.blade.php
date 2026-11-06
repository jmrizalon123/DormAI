<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('layouts.partials.theme-assets')
</head>
<body class="da-guest">
    <header class="da-public-nav py-3 mb-4">
        <div class="container">
            <a href="{{ route('home') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
                <span class="rounded-3 bg-white border d-inline-flex align-items-center justify-content-center text-primary" style="width:40px;height:40px;"><i class="bi bi-buildings"></i></span>
                <span class="da-auth-logo fs-5">{{ config('app.name') }}</span>
            </a>
        </div>
    </header>
    <main class="container pb-5">
        @include('components.flash-messages')
        @yield('content')
    </main>
    @include('components.da-confirm-modal')
    <div id="daToastHost" class="da-toast-host" aria-live="polite" aria-atomic="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('layouts.partials.dormai-feedback-scripts')
</body>
</html>
