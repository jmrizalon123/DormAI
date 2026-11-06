@extends('layouts.app')
@section('title', 'Maintenance')
@section('page-title', 'Maintenance requests')
@section('page-actions')<a href="{{ route('portal.maintenance.create') }}" class="btn btn-primary btn-sm">New request</a>@endsection
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><x-da-table-num-th /><th>Ref</th><th>Issue</th><th>Priority</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($maintenanceRequests as $item)
                        <tr>
                            <x-da-table-num-td :paginator="$maintenanceRequests" :row="$loop->index" />
                            <td class="font-monospace small">{{ $item->reference_code }}</td>
                            <td>{{ $item->title }}</td>
                            <td><span class="badge text-bg-{{ $item->priority->badgeClass() }}">{{ $item->priority->label() }}</span></td>
                            <td>{{ $item->status->label() }}</td>
                            <td class="text-end"><a href="{{ route('portal.maintenance.show', $item) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="da-empty-state">No requests yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$maintenanceRequests" />
    </div>
@endsection
