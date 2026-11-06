@extends('layouts.app')
@section('title', $maintenanceRequest->reference_code)
@section('page-title', $maintenanceRequest->title)
@section('page-subtitle', $maintenanceRequest->reference_code)
@section('page-actions')
    @can('createWorkOrder', $maintenanceRequest)
        <form method="POST" action="{{ route('organization.maintenance-requests.work-order', $maintenanceRequest) }}" class="d-inline">@csrf<button class="btn btn-primary btn-sm">Create work order</button></form>
    @endcan
    @can('cancel', $maintenanceRequest)
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">Cancel request</button>
    @endcan
@endsection
@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="da-panel p-4 mb-3">
                <p>{{ $maintenanceRequest->description }}</p>
                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Priority</dt><dd class="col-sm-8"><span class="badge text-bg-{{ $maintenanceRequest->priority->badgeClass() }}">{{ $maintenanceRequest->priority->label() }}</span></dd>
                    <dt class="col-sm-4 text-muted">Status</dt><dd class="col-sm-8">{{ $maintenanceRequest->status->label() }}</dd>
                    <dt class="col-sm-4 text-muted">Location</dt><dd class="col-sm-8">{{ $maintenanceRequest->property->name }}@if($maintenanceRequest->room) · Room {{ $maintenanceRequest->room->room_number }}@endif</dd>
                </dl>
            </div>
            @if($maintenanceRequest->workOrder)
                <a href="{{ route('organization.work-orders.show', $maintenanceRequest->workOrder) }}" class="btn btn-outline-primary btn-sm">Open work order {{ $maintenanceRequest->workOrder->work_order_number }}</a>
            @endif
        </div>
        <div class="col-lg-5">
            <div class="da-panel p-3">
                <h2 class="h6">History</h2>
                <ul class="list-unstyled small mb-0">@foreach($maintenanceRequest->logs as $log)<li class="py-2 border-bottom"><div class="fw-semibold">{{ $log->message }}</div><div class="text-muted">{{ $log->user->name }} · {{ $log->created_at->format('M j, g:i A') }}</div></li>@endforeach</ul>
            </div>
        </div>
    </div>
    @can('cancel', $maintenanceRequest)
        <div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-labelledby="cancelRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('organization.maintenance-requests.cancel', $maintenanceRequest) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="cancelRequestModalLabel">Cancel maintenance request</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="cancel-reason" class="form-label">Reason</label>
                        <textarea name="reason" id="cancel-reason" class="form-control" rows="3" required maxlength="500" placeholder="Why is this request being cancelled?"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep open</button>
                        <button type="submit" class="btn btn-danger">Cancel request</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection
