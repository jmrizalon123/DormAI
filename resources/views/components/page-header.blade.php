@if (! empty($title ?? null) || ! empty($subtitle ?? null))
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            @if (! empty($title ?? null))
                <h1 class="h3 mb-1">{{ $title }}</h1>
            @endif
            @if (! empty($subtitle ?? null))
                <p class="text-muted mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if (! empty($actions ?? null))
            <div class="d-flex gap-2">{!! $actions !!}</div>
        @endif
    </div>
@endif
