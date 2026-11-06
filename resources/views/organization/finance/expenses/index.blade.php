@extends('layouts.app')
@section('title', 'Expenses')
@section('page-title', 'Expenses')
@section('page-subtitle', 'Pending approval: ₱'.number_format($pendingTotal, 2))
@section('page-actions')
    @can('create', App\Models\Expense::class)
        <a href="{{ route('organization.expenses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Record expense</a>
    @endcan
@endsection
@section('content')
    <div class="da-filter-bar">
        <form method="GET" class="row g-3 da-filter-row">
            <div class="col-md-4"><label class="form-label">Search</label><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Description, vendor, ref #"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(App\Enums\ExpenseStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Category</label><select name="category" class="form-select"><option value="">All</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected((string) request('category')===(string) $cat->id)>{{ $cat->name }}</option>@endforeach</select></div>
            <div class="col-md-2 da-filter-submit"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Date</th><th>Description</th><th>Category</th><th>Property</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <x-da-table-num-td :paginator="$expenses" :row="$loop->index" />
                            <td>{{ $expense->expense_date->format('M j, Y') }}</td>
                            <td><div class="fw-semibold">{{ $expense->description }}</div>@if($expense->vendor)<div class="small text-muted">{{ $expense->vendor }}</div>@endif</td>
                            <td>{{ $expense->category->name }}</td>
                            <td class="small text-muted">{{ $expense->property?->name ?? '—' }}</td>
                            <td class="fw-semibold">₱{{ number_format($expense->amount, 2) }}</td>
                            <td><span class="badge text-bg-{{ $expense->status->badgeClass() }}">{{ $expense->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.expenses.show', $expense) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="da-empty-state"><i class="bi bi-wallet2"></i>No expenses recorded yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$expenses" />
    </div>
@endsection
