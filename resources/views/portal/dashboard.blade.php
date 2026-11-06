@extends('layouts.app')
@section('title', 'Tenant dashboard')
@section('page-title', 'Welcome, '.$tenant->first_name)
@section('page-subtitle', auth()->user()->organization->name)
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="da-stat-card"><div class="small text-muted">Outstanding balance</div><div class="fs-4 fw-bold">₱{{ number_format($stats['balance'], 2) }}</div></div></div>
        <div class="col-md-4"><div class="da-stat-card"><div class="small text-muted">Open maintenance requests</div><div class="fs-4 fw-bold">{{ $stats['open_requests'] }}</div></div></div>
        <div class="col-md-4"><div class="da-stat-card"><div class="small text-muted">Room assignment</div><div class="fs-6 fw-semibold">@if($tenant->activeAssignment){{ $tenant->activeAssignment->property->name }} · Rm {{ $tenant->activeAssignment->room->room_number }} · {{ $tenant->activeAssignment->bedSpace->label }}@else<span class="text-muted">Not assigned</span>@endif</div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="da-panel p-4 mb-3">
                <h2 class="h6 fw-bold">Quick actions</h2>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="{{ route('portal.payment-submissions.create') }}" class="btn btn-primary btn-sm">Submit payment reference</a>
                    <a href="{{ route('portal.maintenance.create') }}" class="btn btn-outline-primary btn-sm">Report maintenance issue</a>
                    <a href="{{ route('portal.invoices.index') }}" class="btn btn-outline-secondary btn-sm">View invoices</a>
                </div>
            </div>
            @if($activeContract)
                <div class="da-panel p-4">
                    <h2 class="h6 fw-bold">Active rental agreement</h2>
                    <p class="mb-2 small">{{ $activeContract->contract_number }} · ₱{{ number_format($activeContract->monthly_rate, 2) }}/month</p>
                    <a href="{{ route('portal.contracts.show', $activeContract) }}" class="btn btn-sm btn-outline-secondary">View agreement</a>
                </div>
            @endif
        </div>
        <div class="col-lg-5">
            <div class="da-panel p-4">
                <h2 class="h6 fw-bold">Announcements</h2>
                <ul class="list-unstyled mb-0 small">
                    @forelse($announcements as $item)
                        <li class="py-2 border-bottom"><a href="{{ route('portal.announcements.show', $item) }}" class="fw-semibold text-decoration-none">{{ $item->title }}</a><div class="text-muted">{{ $item->published_at?->format('M j, Y') }}</div></li>
                    @empty
                        <li class="text-muted py-2">No announcements yet.</li>
                    @endforelse
                </ul>
                <a href="{{ route('portal.announcements.index') }}" class="btn btn-link btn-sm px-0 mt-2">View all</a>
            </div>
        </div>
    </div>
@endsection
