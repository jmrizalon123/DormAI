@extends('layouts.app')
@section('title', 'Maintenance history')
@section('page-title', 'Maintenance history')
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="small text-muted">Open work orders</div><div class="fs-4 fw-bold">{{ $stats['open'] }}</div></div></div>
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="small text-muted">Completed this month</div><div class="fs-4 fw-bold">{{ $stats['completed_mtd'] }}</div></div></div>
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="small text-muted">Repair cost (MTD)</div><div class="fs-4 fw-bold">₱{{ number_format($stats['cost_mtd'], 2) }}</div></div></div>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><x-da-table-num-th /><th>When</th><th>Event</th><th>Work order</th><th>By</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <x-da-table-num-td :paginator="$logs" :row="$loop->index" />
                            <td class="text-muted">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td>{{ $log->message }}</td>
                            <td>@if($log->workOrder)<a href="{{ route('organization.work-orders.show', $log->workOrder) }}">{{ $log->workOrder->work_order_number }}</a>@else—@endif</td>
                            <td>{{ $log->user->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted p-3">No maintenance activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$logs" />
    </div>
@endsection
