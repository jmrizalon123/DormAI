@props([
    'variant' => 'full',
    'surface' => 'dark',
    'brandPrimary' => null,
    'brandAccent' => null,
    /** When true with variant wordmark, trims the empty space reserved for the mark so it sits flush beside a separate mark SVG. */
    'wordmarkCrop' => false,
])

@php
    $gradientId = 'da-logo-grad-'.substr(md5((string) $attributes), 0, 8);
    $showMark = in_array($variant, ['full', 'mark'], true);
    $showWord = in_array($variant, ['full', 'wordmark'], true);
    $viewBox = $variant === 'mark' ? '0 0 36 36' : ($showWord ? '0 0 156 36' : '0 0 36 36');
    if ($variant === 'wordmark' && $wordmarkCrop) {
        $viewBox = '42 0 114 36';
    }
    $wordPrimary = $surface === 'light' ? '#115e59' : '#fafafa';
    $wordAccent = $surface === 'light' ? '#7c3aed' : '#5eead4';
    $wordPrimaryText = filled($brandPrimary) ? (string) $brandPrimary : 'Dorm';
    $wordAccentText = filled($brandAccent) ? (string) $brandAccent : 'AI';
@endphp

<svg
    {{ $attributes->merge(['class' => 'da-logo']) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="{{ $viewBox }}"
    data-da-logo-view-full="0 0 156 36"
    data-da-logo-view-mark="0 0 36 36"
    role="img"
    aria-label="{{ config('app.name') }}"
>
    @if($showMark)
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#5eead4"/>
                <stop offset="100%" stop-color="#a78bfa"/>
            </linearGradient>
        </defs>
        <rect x="2" y="2" width="32" height="32" rx="10" fill="url(#{{ $gradientId }})"/>
        <path fill="#0c0c0e" d="M11 26V14h5.5c3.2 0 5.3 1.8 5.3 4.6 0 2.1-1.2 3.6-3.1 4.2L23 26h-2.5l-1.8-5.6H13.2V26H11zm2.2-7.4h2.2c1.2 0 1.9-.6 1.9-1.5s-.7-1.4-1.9-1.4h-2.2v2.9z"/>
        <circle cx="25.5" cy="10.5" r="2" fill="#fef9c3"/>
    @endif
    @if($showWord)
        <g class="da-logo-text">
            <text x="44" y="25" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="21" font-weight="700" letter-spacing="-0.04em">
                <tspan fill="{{ $wordPrimary }}">{{ $wordPrimaryText }}</tspan><tspan fill="{{ $wordAccent }}">{{ $wordAccentText }}</tspan>
            </text>
        </g>
    @endif
</svg>
