@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script defer src="{{ asset('js/map-fullscreen.js') }}@if(file_exists(public_path('js/map-fullscreen.js')))?v={{ filemtime(public_path('js/map-fullscreen.js')) }}@endif"></script>
    @endpush
@endonce
