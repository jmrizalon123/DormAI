@props([
    'href',
    'icon',
    'active' => false,
    'label',
    /** Pending or alert count shown as a small pill (e.g. new reservations) */
    'badgeCount' => null,
])

<a
    href="{{ $href }}"
    @class([
        'nav-link da-sidebar-row d-flex align-items-center gap-2',
        'is-active' => $active,
    ])
    aria-label="{{ $label }}"
    data-sidebar-tooltip="{{ $label }}"
>
    <i class="bi {{ $icon }} da-sidebar-row-icon" aria-hidden="true"></i>
    <span class="da-sidebar-label">{{ $label }}</span>
    @if(filled($badgeCount) && (int) $badgeCount > 0)
        <span class="badge rounded-pill text-bg-warning da-sidebar-nav-badge ms-auto" title="Pending items">{{ (int) $badgeCount > 99 ? '99+' : (int) $badgeCount }}</span>
    @endif
</a>
