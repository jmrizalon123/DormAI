@extends('layouts.app')
@section('title', 'Maintenance requests')
@section('page-title', 'Tenant maintenance requests')
@section('page-actions')@can('create', App\Models\MaintenanceRequest::class)<a href="{{ route('organization.maintenance-requests.create') }}" class="btn btn-primary btn-sm">Log request</a>@endcan@endsection
@section('content')
    <div class="da-filter-bar mb-3">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(App\Enums\MaintenanceRequestStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><select name="priority" class="form-select"><option value="">All priorities</option>@foreach(App\Enums\MaintenancePriority::cases() as $p)<option value="{{ $p->value }}" @selected(request('priority')===$p->value)>{{ $p->label() }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Ref</th><th>Issue</th><th>Location</th><th>Priority</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($maintenanceRequests as $item)
                        <tr>
                            <x-da-table-num-td :paginator="$maintenanceRequests" :row="$loop->index" />
                            <td class="font-monospace small">{{ $item->reference_code }}</td>
                            <td><div class="fw-semibold">{{ $item->title }}</div>@if($item->tenant)<div class="small text-muted">{{ $item->tenant->fullName() }}</div>@endif</td>
                            <td class="small">{{ $item->property->name }}@if($item->room) · Room {{ $item->room->room_number }}@endif</td>
                            <td><span class="badge text-bg-{{ $item->priority->badgeClass() }}">{{ $item->priority->label() }}</span></td>
                            <td><span class="badge text-bg-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.maintenance-requests.show', $item) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="da-empty-state">No maintenance requests yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$maintenanceRequests" />
    </div>
@endsection
