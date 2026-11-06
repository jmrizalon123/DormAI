@extends('layouts.app')
@section('title', 'Tenants — '.config('app.name'))
@section('page-title', 'Tenants')
@section('page-subtitle', 'Register and manage dormitory tenants')
@section('page-actions')<a href="{{ route('organization.tenants.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Register tenant</a>@endsection
@section('content')
    <div class="da-filter-bar">
        <form method="GET" class="row g-3 da-filter-row">
            <div class="col-md-4"><label class="form-label">Search</label><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email, phone"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(App\Enums\TenantStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Property</label><select name="property_id" class="form-select"><option value="">All</option>@foreach($properties as $property)<option value="{{ $property->id }}" @selected((string)request('property_id')===(string)$property->id)>{{ $property->name }}</option>@endforeach</select></div>
            <div class="col-md-2 da-filter-submit"><button type="submit" class="btn btn-primary w-100">Apply filters</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Tenant</th><th>Contact</th><th>Assignment</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <x-da-table-num-td :paginator="$tenants" :row="$loop->index" />
                            <td><div class="fw-semibold">{{ $tenant->fullName() }}</div>@if($tenant->school_or_work)<div class="small text-muted">{{ $tenant->school_or_work }}</div>@endif</td>
                            <td class="small"><div>{{ $tenant->phone ?: '—' }}</div><div class="text-muted">{{ $tenant->email ?: '—' }}</div></td>
                            <td class="small">
                                @if($tenant->activeAssignment)
                                    {{ $tenant->activeAssignment->property->name }} · Room {{ $tenant->activeAssignment->room->room_number }} · Bed {{ $tenant->activeAssignment->bedSpace->label }}
                                @else — @endif
                            </td>
                            <td><span class="badge text-bg-{{ $tenant->status->badgeClass() }}">{{ $tenant->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.tenants.show', $tenant) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No tenants match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$tenants" />
    </div>
@endsection
