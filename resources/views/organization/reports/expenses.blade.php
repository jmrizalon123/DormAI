@extends('layouts.app')
@section('title', 'Expense analytics')
@section('page-title', 'Expense analytics')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="text-muted small">Approved (period)</div><div class="fs-4 fw-bold text-danger">₱{{ number_format($report['approved_total'], 2) }}</div></div></div>
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="text-muted small">Pending approval</div><div class="fs-4 fw-bold">{{ $report['pending_count'] }} · ₱{{ number_format($report['pending_amount'], 2) }}</div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="da-panel p-4 h-100">
                <h2 class="h6 mb-3">By category</h2>
                <x-reports.bar-chart
                    id="expenses-by-category"
                    :labels="array_keys($report['by_category'])"
                    :datasets="[[
                        'label' => 'Amount',
                        'data' => array_values($report['by_category']),
                        'backgroundColor' => 'rgba(220, 53, 69, 0.55)',
                    ]]"
                />
            </div>
        </div>
        <div class="col-lg-7">
            <div class="da-panel p-4 h-100">
                <h2 class="h6 mb-3">Recent approved expenses</h2>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date</th><th>Category</th><th>Description</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            @forelse($report['recent'] as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('M j, Y') }}</td>
                                    <td>{{ $expense->category?->name ?? '—' }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td class="text-end">₱{{ number_format((float) $expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">No approved expenses in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
