@props(['paginator' => null, 'row' => 0])

@php
    $number = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $paginator->firstItem() + (int) $row
        : (int) $row + 1;
@endphp

<td {{ $attributes->merge(['class' => 'da-table-num text-end text-muted small']) }}>{{ $number }}</td>
