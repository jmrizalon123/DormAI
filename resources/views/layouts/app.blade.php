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
    @stack('styles')
    <script>
        try {
            if (localStorage.getItem('dormai.theme.v1') === 'dark') {
                document.documentElement.setAttribute('data-da-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        } catch (e) {}
    </script>
</head>
<body class="da-app bg-light">
    @include('layouts.partials.topbar-progress')
    <div class="app-shell d-flex">
        <div class="offcanvas-lg offcanvas-start app-sidebar flex-shrink-0 text-white" tabindex="-1" id="appSidebar" aria-label="{{ config('app.name') }} navigation">
            <div class="offcanvas-header border-bottom border-secondary border-opacity-25 d-lg-none">
                <a class="da-sidebar-brand-link flex-grow-1" href="{{ route('dashboard') }}">
                    <x-brand.dormai-logo variant="full" class="da-sidebar-logo da-sidebar-logo--mobile" />
                </a>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-0">
                <div class="d-none d-lg-block">
                    @include('layouts.partials.sidebar-brand')
                </div>
                <div class="da-sidebar-scroll">
                    @include('layouts.partials.sidebar-nav')
                </div>
                @include('layouts.partials.sidebar-footer')
            </div>
        </div>

        <div class="app-main d-flex flex-column">
            <header class="app-topbar sticky-top d-flex align-items-center px-3 px-lg-4">
                <button
                    class="da-sidebar-toggle me-2"
                    type="button"
                    id="daSidebarToggle"
                    aria-controls="appSidebar"
                    aria-expanded="false"
                    aria-label="Open navigation menu"
                    title="Menu"
                    data-da-sidebar-toggle
                >
                    <span class="da-sidebar-toggle-bars" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <a href="{{ route('dashboard') }}" class="da-topbar-brand d-inline-flex align-items-center text-decoration-none" aria-label="{{ config('app.name') }} home">
                    <x-brand.dormai-logo variant="mark" class="da-topbar-logo da-topbar-logo--mark d-sm-none" />
                    <x-brand.dormai-logo variant="full" class="da-topbar-logo d-none d-sm-block" />
                </a>
                <div class="ms-auto d-flex align-items-center gap-2">
                    @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                    <a
                        href="{{ route('notifications.index') }}"
                        class="btn btn-light btn-sm position-relative da-notifications-btn d-inline-flex"
                        aria-label="Notifications{{ $unreadNotifications ? ", {$unreadNotifications} unread" : '' }}"
                    >
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        @if($unreadNotifications)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger da-notifications-badge">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </span>
                        @endif
                    </a>
                    <div class="dropdown">
                        <a class="btn btn-light btn-sm dropdown-toggle d-inline-flex align-items-center gap-2 da-topbar-user-btn" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;font-size:0.75rem;font-weight:700;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="d-none d-md-inline text-truncate da-topbar-user-name">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" data-da-confirm="Sign out of your account?" data-da-confirm-title="Sign out">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="flex-grow-1 px-3 px-lg-4 py-4">
                <div class="da-page-header mb-4 d-flex flex-column gap-2 align-items-stretch">
                    <div class="da-back-nav">
                        <x-ui.back-link />
                    </div>
                    @hasSection('page-title')
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 w-100">
                            <div class="min-w-0">
                                <h1 class="h3 mb-1">@yield('page-title')</h1>
                                @hasSection('page-subtitle')
                                    <p class="da-lead mb-0">@yield('page-subtitle')</p>
                                @endif
                            </div>
                            @hasSection('page-actions')
                                <div class="d-flex flex-wrap gap-2">@yield('page-actions')</div>
                            @endif
                        </div>
                    @endif
                </div>
                @include('components.flash-messages')
                @yield('content')
            </main>
        </div>
    </div>

    @include('components.da-confirm-modal')

    <div id="daToastHost" class="da-toast-host" aria-live="polite" aria-atomic="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('layouts.partials.dormai-feedback-scripts')
    @stack('scripts')
</body>
</html>
