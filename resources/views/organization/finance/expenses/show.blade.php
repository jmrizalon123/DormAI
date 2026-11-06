@extends('layouts.app')
@section('title', 'Expense details')
@section('page-title', $expense->description)
@section('page-subtitle', $expense->expense_date->format('M j, Y').' · '.$expense->category->name)
@section('page-actions')
    @can('update', $expense)
        <a href="{{ route('organization.expenses.edit', $expense) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    @endcan
    @if($expense->status === App\Enums\ExpenseStatus::Draft)
        @can('submit', $expense)
            <form method="POST" action="{{ route('organization.expenses.submit', $expense) }}" class="d-inline">@csrf<button type="submit" class="btn btn-primary btn-sm">Submit for approval</button></form>
        @endcan
    @endif
@endsection
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge text-bg-{{ $expense->status->badgeClass() }}">{{ $expense->status->label() }}</span>
                    <div class="fs-4 fw-bold">₱{{ number_format($expense->amount, 2) }}</div>
                </div>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Vendor</dt><dd class="col-sm-8">{{ $expense->vendor ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Reference</dt><dd class="col-sm-8">{{ $expense->reference_number ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Property</dt><dd class="col-sm-8">{{ $expense->property?->name ?? 'Organization-wide' }}</dd>
                    <dt class="col-sm-4 text-muted">Recorded by</dt><dd class="col-sm-8">{{ $expense->recorder->name }}</dd>
                    @if($expense->approver)<dt class="col-sm-4 text-muted">Approved by</dt><dd class="col-sm-8">{{ $expense->approver->name }} · {{ $expense->approved_at?->format('M j, Y g:i A') }}</dd>@endif
                    @if($expense->rejection_reason)<dt class="col-sm-4 text-muted">Rejection</dt><dd class="col-sm-8 text-danger">{{ $expense->rejection_reason }}</dd>@endif
                </dl>
                @if($expense->notes)<p class="mt-3 mb-0 text-muted">{{ $expense->notes }}</p>@endif
            </div>

            @if($expense->status === App\Enums\ExpenseStatus::PendingApproval)
                @can('approve', $expense)
                    <div class="da-panel p-4 mb-4 border-warning">
                        <h2 class="h6">Approval</h2>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('organization.expenses.approve', $expense) }}">@csrf<button type="submit" class="btn btn-success btn-sm">Approve</button></form>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#rejectForm">Reject</button>
                        </div>
                        <div class="collapse mt-3" id="rejectForm">
                            <form method="POST" action="{{ route('organization.expenses.reject', $expense) }}">
                                @csrf
                                <label class="form-label">Reason</label>
                                <textarea name="rejection_reason" class="form-control mb-2" rows="2" required></textarea>
                                <button type="submit" class="btn btn-danger btn-sm">Confirm rejection</button>
                            </form>
                        </div>
                    </div>
                @endcan
            @endif

            <div class="da-panel p-4">
                <h2 class="h6 mb-3">Attachments</h2>
                @forelse($expense->attachments as $attachment)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <a href="{{ route('organization.expenses.attachments.show', [$expense, $attachment]) }}">{{ $attachment->original_name }}</a>
                        @can('attach', $expense)
                            <form method="POST" action="{{ route('organization.expenses.attachments.destroy', [$expense, $attachment]) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Remove</button></form>
                        @endcan
                    </div>
                @empty
                    <p class="text-muted small mb-0">No attachments yet.</p>
                @endforelse
                @can('attach', $expense)
                    <form method="POST" action="{{ route('organization.expenses.attachments.store', $expense) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            <button type="submit" class="btn btn-outline-primary">Upload</button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@endsection
