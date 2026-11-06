@extends('layouts.app')
@section('title', $workOrder->work_order_number)
@section('page-title', $workOrder->title)
@section('page-subtitle', $workOrder->work_order_number.' · '.$workOrder->property->name)
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel p-4 mb-3">
                <div class="d-flex gap-2 mb-3">
                    <span class="badge text-bg-{{ $workOrder->priority->badgeClass() }}">{{ $workOrder->priority->label() }}</span>
                    <span class="badge text-bg-{{ $workOrder->status->badgeClass() }}">{{ $workOrder->status->label() }}</span>
                </div>
                @if($workOrder->description)<p class="mb-0">{{ $workOrder->description }}</p>@endif
            </div>
            @can('assign', $workOrder)
                @if($workOrder->isOpen())
                    <div class="da-panel p-4 mb-3">
                        <h2 class="h6">Assign staff</h2>
                        <form method="POST" action="{{ route('organization.work-orders.assign', $workOrder) }}" class="row g-2">@csrf
                            <div class="col-md-8"><select name="assigned_to" class="form-select" required><option value="">Select staff</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected($workOrder->assigned_to===$member->id)>{{ $member->name }}</option>@endforeach</select></div>
                            <div class="col-md-4"><button class="btn btn-primary w-100">Assign</button></div>
                        </form>
                    </div>
                    <div class="da-panel p-4 mb-3">
                        <h2 class="h6">Update status</h2>
                        <form method="POST" action="{{ route('organization.work-orders.status', $workOrder) }}">@csrf
                            <div class="row g-2">
                                <div class="col-md-4"><select name="status" class="form-select">@foreach([App\Enums\WorkOrderStatus::InProgress, App\Enums\WorkOrderStatus::OnHold, App\Enums\WorkOrderStatus::Cancelled] as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach</select></div>
                                <div class="col-md-5"><input type="text" name="note" class="form-control" placeholder="Optional note"></div>
                                <div class="col-md-3"><button class="btn btn-outline-secondary w-100">Update</button></div>
                            </div>
                        </form>
                    </div>
                    <div class="da-panel p-4">
                        <h2 class="h6">Complete & record costs</h2>
                        <form method="POST" action="{{ route('organization.work-orders.complete', $workOrder) }}">@csrf
                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label">Labor (₱)</label><input type="number" step="0.01" min="0" name="labor_cost" class="form-control" value="0" required></div>
                                <div class="col-md-3"><label class="form-label">Parts (₱)</label><input type="number" step="0.01" min="0" name="parts_cost" class="form-control" value="0" required></div>
                                <div class="col-md-6"><label class="form-label">Completion notes</label><input type="text" name="completion_notes" class="form-control"></div>
                            </div>
                            <button type="submit" class="btn btn-success mt-3">Mark completed</button>
                        </form>
                    </div>
                @endif
            @endcan
        </div>
        <div class="col-lg-4">
            <div class="da-panel p-3 mb-3">
                <dl class="small mb-0">
                    <dt class="text-muted">Assignee</dt><dd>{{ $workOrder->assignee?->name ?? 'Unassigned' }}</dd>
                    <dt class="text-muted">Total cost</dt><dd class="fw-bold">₱{{ number_format($workOrder->total_cost, 2) }}</dd>
                </dl>
            </div>
            <div class="da-panel p-3">
                <h2 class="h6">Repair history</h2>
                <ul class="list-unstyled small mb-0">@foreach($workOrder->logs as $log)<li class="py-2 border-bottom"><div>{{ $log->message }}</div><div class="text-muted">{{ $log->user->name }} · {{ $log->created_at->diffForHumans() }}</div></li>@endforeach</ul>
            </div>
        </div>
    </div>
@endsection
