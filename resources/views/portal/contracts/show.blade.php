@extends('layouts.app')
@section('title', $contract->contract_number)
@section('page-title', 'Contract '.$contract->contract_number)
@section('content')
    <div class="da-panel p-4">
        <dl class="row small mb-0">
            <dt class="col-sm-4 text-muted">Property</dt><dd class="col-sm-8">{{ $contract->property->name }}</dd>
            <dt class="col-sm-4 text-muted">Room / bed</dt><dd class="col-sm-8">@if($contract->room)Room {{ $contract->room->room_number }}@endif @if($contract->bedSpace)· {{ $contract->bedSpace->label }}@endif</dd>
            <dt class="col-sm-4 text-muted">Monthly rate</dt><dd class="col-sm-8">₱{{ number_format($contract->monthly_rate, 2) }}</dd>
            <dt class="col-sm-4 text-muted">Term</dt><dd class="col-sm-8">{{ $contract->start_date->format('M j, Y') }} – {{ $contract->end_date?->format('M j, Y') ?? 'Open-ended' }}</dd>
            <dt class="col-sm-4 text-muted">Status</dt><dd class="col-sm-8">{{ $contract->status->label() }}</dd>
        </dl>
        @if($contract->terms)<hr><div class="small">{!! nl2br(e($contract->terms)) !!}</div>@endif
    </div>
@endsection
