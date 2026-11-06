@props(['paginator'])

@if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $paginator->total() > 0)
    <div {{ $attributes->merge(['class' => 'da-panel-footer']) }}>
        {{ $paginator->withQueryString()->links('vendor.pagination.dormai') }}
    </div>
@endif
