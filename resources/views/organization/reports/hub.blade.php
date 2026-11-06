@extends('layouts.app')
@section('title', 'Reports & analytics')
@section('page-title', 'Reports & analytics')
@section('page-subtitle', 'Financial, occupancy, collections, and operations')
@section('content')
    <div class="row g-3">
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('organization.reports.occupancy') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="da-icon-tile"><i class="bi bi-house-door"></i></span>
                    <h2 class="h6 mb-0 text-body">Occupancy</h2>
                </div>
                <p class="text-muted small mb-0">Bed utilization by property and organization-wide rate.</p>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('organization.reports.payment-collection') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="da-icon-tile"><i class="bi bi-cash-coin"></i></span>
                    <h2 class="h6 mb-0 text-body">Payment collection</h2>
                </div>
                <p class="text-muted small mb-0">Invoiced vs collected, open balances, and monthly trends.</p>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('organization.reports.tenants') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="da-icon-tile"><i class="bi bi-people"></i></span>
                    <h2 class="h6 mb-0 text-body">Tenants</h2>
                </div>
                <p class="text-muted small mb-0">Active assignments, move-ins, and move-outs for a period.</p>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('organization.reports.reservations') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="da-icon-tile"><i class="bi bi-calendar-check"></i></span>
                    <h2 class="h6 mb-0 text-body">Reservations</h2>
                </div>
                <p class="text-muted small mb-0">Request volume by status and check-in conversion.</p>
            </a>
        </div>
        @if($canFinancial)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('organization.financial-reports.summary') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="da-icon-tile"><i class="bi bi-graph-up"></i></span>
                        <h2 class="h6 mb-0 text-body">Financial summary</h2>
                    </div>
                    <p class="text-muted small mb-0">Income, expenses, net P&amp;L, and monthly trend charts.</p>
                </a>
            </div>
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('organization.reports.expenses') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="da-icon-tile"><i class="bi bi-journal-minus"></i></span>
                        <h2 class="h6 mb-0 text-body">Expense analytics</h2>
                    </div>
                    <p class="text-muted small mb-0">Approved spend by category with recent line items.</p>
                </a>
            </div>
        @endif
        @if($canUtilities)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('organization.utility-reports.index') }}" class="da-panel p-4 d-block text-decoration-none h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="da-icon-tile"><i class="bi bi-lightning"></i></span>
                        <h2 class="h6 mb-0 text-body">Utility consumption</h2>
                    </div>
                    <p class="text-muted small mb-0">Meter readings, consumption, and billing by property.</p>
                </a>
            </div>
        @endif
    </div>
@endsection
