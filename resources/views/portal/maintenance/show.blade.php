@extends('layouts.app')
@section('title', $maintenanceRequest->reference_code)
@section('page-title', $maintenanceRequest->title)
@section('page-subtitle', $maintenanceRequest->reference_code)
@section('page-actions')
    @if($maintenanceRequest->canBeCancelled())
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelRequestModal">Cancel</button>
    @endif
@endsection
@section('content')
    <div class="da-panel p-4 mb-3">
        <p>{{ $maintenanceRequest->description }}</p>
        <span class="badge text-bg-{{ $maintenanceRequest->priority->badgeClass() }}">{{ $maintenanceRequest->priority->label() }}</span>
        <span class="ms-2">{{ $maintenanceRequest->status->label() }}</span>
        @if($maintenanceRequest->workOrder)<p class="small text-muted mt-2 mb-0">Work order {{ $maintenanceRequest->workOrder->work_order_number }} — {{ $maintenanceRequest->workOrder->status->label() }}</p>@endif
    </div>
    @if($maintenanceRequest->canBeCancelled())
        <div class="modal fade" id="cancelRequestModal" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('portal.maintenance.cancel', $maintenanceRequest) }}" class="modal-content">@csrf
            <div class="modal-header"><h1 class="modal-title fs-5">Cancel request</h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><textarea name="reason" class="form-control" rows="3" required placeholder="Reason"></textarea></div>
            <div class="modal-footer"><button type="submit" class="btn btn-danger">Cancel request</button></div>
        </form></div></div>
    @endif
@endsection
