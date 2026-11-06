@props(['label', 'value', 'icon' => 'bi-grid'])

<div class="da-stat-card">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            <div class="da-stat-label mb-2">{{ $label }}</div>
            <div class="da-stat-value">{{ $value }}</div>
        </div>
        <div class="da-stat-icon flex-shrink-0">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>
</div>
