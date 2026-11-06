@props([
    'id',
    'label',
    'icon',
    'open' => false,
    'active' => false,
])

<div class="da-nav-group">
    <button
        type="button"
        @class([
            'da-nav-group-toggle da-sidebar-row',
            'is-active' => $active,
        ])
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        aria-controls="{{ $id }}-menu"
        aria-label="{{ $label }}"
        data-nav-group-toggle="{{ $id }}"
        data-sidebar-tooltip="{{ $label }}"
    >
        <i class="bi {{ $icon }} da-sidebar-row-icon" aria-hidden="true"></i>
        <span class="da-nav-group-label da-sidebar-label">{{ $label }}</span>
        <i class="bi bi-chevron-down da-nav-chevron ms-auto" aria-hidden="true"></i>
    </button>
    <div
        id="{{ $id }}-menu"
        class="da-nav-group-panel {{ $open ? 'is-open' : '' }}"
        data-nav-group-panel="{{ $id }}"
        role="region"
        aria-label="{{ $label }}"
    >
        <p class="da-nav-flyout-title">{{ $label }}</p>
        <ul class="da-nav-group-menu list-unstyled mb-0">
            {{ $slot }}
        </ul>
    </div>
</div>
