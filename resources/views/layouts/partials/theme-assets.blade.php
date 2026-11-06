@php
    $viteBuilt = file_exists(public_path('build/manifest.json'));
@endphp

@if ($viteBuilt)
    @vite(['resources/css/app.css', 'resources/css/dormai-theme.css', 'resources/js/app.js'])
@else
    @if (file_exists(public_path('css/dormai-theme.css')))
        <link rel="stylesheet" href="{{ asset('css/dormai-theme.css') }}?v={{ filemtime(public_path('css/dormai-theme.css')) }}">
    @endif
@endif

@if (! $viteBuilt && file_exists(public_path('js/topbar-progress.js')))
    <script defer src="{{ asset('js/topbar-progress.js') }}?v={{ filemtime(public_path('js/topbar-progress.js')) }}"></script>
@endif

@if (! $viteBuilt && file_exists(public_path('js/sidebar-nav.js')))
    <script defer src="{{ asset('js/sidebar-nav.js') }}?v={{ filemtime(public_path('js/sidebar-nav.js')) }}"></script>
@endif

@if (! $viteBuilt && file_exists(public_path('js/sidebar-collapse.js')))
    <script defer src="{{ asset('js/sidebar-collapse.js') }}?v={{ filemtime(public_path('js/sidebar-collapse.js')) }}"></script>
@endif

@if (! $viteBuilt && file_exists(public_path('js/theme-mode.js')))
    <script defer src="{{ asset('js/theme-mode.js') }}?v={{ filemtime(public_path('js/theme-mode.js')) }}"></script>
@endif

@if (! $viteBuilt && file_exists(public_path('js/sidebar-rail-flyout.js')))
    <script defer src="{{ asset('js/sidebar-rail-flyout.js') }}?v={{ filemtime(public_path('js/sidebar-rail-flyout.js')) }}"></script>
@endif

@if (! $viteBuilt && file_exists(public_path('js/responsive-tables.js')))
    <script defer src="{{ asset('js/responsive-tables.js') }}?v={{ filemtime(public_path('js/responsive-tables.js')) }}"></script>
@endif
