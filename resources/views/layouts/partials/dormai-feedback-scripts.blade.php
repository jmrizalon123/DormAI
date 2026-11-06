@php
    $daJsVersion = function (string $file): string {
        $path = public_path('js/'.$file);

        return file_exists($path) ? '?v='.filemtime($path) : '';
    };
@endphp
@if (file_exists(public_path('js/dormai-toast.js')))
    <script src="{{ asset('js/dormai-toast.js') }}{{ $daJsVersion('dormai-toast.js') }}"></script>
@endif
@if (file_exists(public_path('js/dormai-confirm.js')))
    <script src="{{ asset('js/dormai-confirm.js') }}{{ $daJsVersion('dormai-confirm.js') }}"></script>
@endif
@if (file_exists(public_path('js/dormai-flash.js')))
    <script src="{{ asset('js/dormai-flash.js') }}{{ $daJsVersion('dormai-flash.js') }}"></script>
@endif
