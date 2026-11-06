@props([
    'chartId',
    'label',
    'value',
    'icon' => 'bi-grid',
    'accent' => '#2563eb',
    'caption' => null,
    'trend' => null,
    'chart' => [],
])

<div class="da-stat-card da-stat-chart-card">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
        <div class="min-w-0">
            <div class="da-stat-label mb-1">{{ $label }}</div>
            <div class="d-flex flex-wrap align-items-baseline gap-2">
                <div class="da-stat-value">{{ $value }}</div>
                @if ($trend)
                    <span @class([
                        'da-stat-trend',
                        'da-stat-trend--'.$trend['direction'],
                    ])>{{ $trend['text'] }}</span>
                @endif
            </div>
            @if ($caption)
                <div class="da-stat-chart-caption">{{ $caption }}</div>
            @endif
        </div>
        <div class="da-stat-icon flex-shrink-0" style="--da-stat-accent: {{ $accent }};">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>
    <div class="da-stat-chart-wrap">
        <canvas
            id="{{ $chartId }}"
            class="da-stat-chart"
            data-da-chart-mode="spark"
            data-da-chart='@json($chart)'
        ></canvas>
    </div>
</div>
