@extends('layouts.app')
@section('title', 'Utility meters')
@section('page-title', 'Utility meters')
@section('page-actions')<a href="{{ route('organization.utility-meters.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add meter</a>@endsection
@section('content')
    <div class="da-filter-bar mb-3">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><select name="property" class="form-select"><option value="">All properties</option>@foreach($properties as $p)<option value="{{ $p->id }}" @selected(request('property')==$p->id)>{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><select name="type" class="form-select"><option value="">All types</option>@foreach(App\Enums\UtilityType::cases() as $t)<option value="{{ $t->value }}" @selected(request('type')===$t->value)>{{ $t->label() }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><x-da-table-num-th /><th>Label</th><th>Property</th><th>Room</th><th>Type</th><th>Scope</th><th></th></tr></thead>
                <tbody>
                    @forelse($meters as $meter)
                        <tr>
                            <x-da-table-num-td :paginator="$meters" :row="$loop->index" />
                            <td class="fw-semibold">{{ $meter->label }}@if($meter->meter_number)<div class="small text-muted">{{ $meter->meter_number }}</div>@endif</td>
                            <td>{{ $meter->property->name }}</td>
                            <td>{{ $meter->room?->room_number ?? '—' }}</td>
                            <td>{{ $meter->utility_type->label() }}</td>
                            <td><span class="badge text-bg-{{ $meter->is_shared ? 'info' : 'secondary' }}">{{ $meter->is_shared ? 'Shared' : 'Room' }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.utility-meters.edit', $meter) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="da-empty-state">No meters registered.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$meters" />
    </div>
@endsection
