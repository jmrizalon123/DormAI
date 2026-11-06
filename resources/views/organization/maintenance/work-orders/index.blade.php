@extends('layouts.app')
@section('title', 'Work orders')
@section('page-title', 'Work orders')
@section('page-actions')@can('create', App\Models\WorkOrder::class)<a href="{{ route('organization.work-orders.create') }}" class="btn btn-primary btn-sm">New work order</a>@endcan@endsection
@section('content')
    <div class="da-filter-bar mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(App\Enums\WorkOrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><select name="assigned" class="form-select"><option value="">All staff</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected(request('assigned')==$member->id)>{{ $member->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="mine" value="1" id="mine" @checked(request()->boolean('mine'))><label class="form-check-label" for="mine">Assigned to me</label></div></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><x-da-table-num-th /><th>WO #</th><th>Title</th><th>Assignee</th><th>Priority</th><th>Status</th><th>Cost</th><th></th></tr></thead>
                <tbody>
                    @forelse($workOrders as $wo)
                        <tr>
                            <x-da-table-num-td :paginator="$workOrders" :row="$loop->index" />
                            <td class="font-monospace">{{ $wo->work_order_number }}</td>
                            <td>{{ $wo->title }}</td>
                            <td>{{ $wo->assignee?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ $wo->priority->badgeClass() }}">{{ $wo->priority->label() }}</span></td>
                            <td>{{ $wo->status->label() }}</td>
                            <td>@if($wo->status === App\Enums\WorkOrderStatus::Completed)₱{{ number_format($wo->total_cost, 2) }}@else—@endif</td>
                            <td class="text-end"><a href="{{ route('organization.work-orders.show', $wo) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="da-empty-state">No work orders.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$workOrders" />
    </div>
@endsection
