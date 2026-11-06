@props([
    'href',
    'icon',
    'active' => false,
    'label',
])

<a
    href="{{ $href }}"
    @class([
        'nav-link da-sidebar-sublink d-flex align-items-center gap-2',
        'is-active' => $active,
    ])
    aria-label="{{ $label }}"
>
    <i class="bi {{ $icon }} da-sidebar-sublink-icon" aria-hidden="true"></i>
    <span class="da-sidebar-label">{{ $label }}</span>
</a>
