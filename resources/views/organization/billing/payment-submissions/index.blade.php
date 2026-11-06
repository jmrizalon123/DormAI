@extends('layouts.app')
@section('title', 'Payment submissions')
@section('page-title', 'Tenant payment submissions')
@section('content')
    <div class="da-filter-bar mb-3"><form method="GET" class="row g-2"><div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(App\Enums\PaymentSubmissionStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div></form></div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><x-da-table-num-th /><th>Submitted</th><th>Tenant</th><th>Reference</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($submissions as $item)
                        <tr>
                            <x-da-table-num-td :paginator="$submissions" :row="$loop->index" />
                            <td class="small">{{ $item->created_at->format('M j, Y') }}</td>
                            <td>{{ $item->tenant->fullName() }}</td>
                            <td>{{ $item->reference_number }}</td>
                            <td>₱{{ number_format($item->amount, 2) }}</td>
                            <td><span class="badge text-bg-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.payment-submissions.show', $item) }}" class="btn btn-sm btn-outline-secondary">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted p-3">No submissions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$submissions" />
    </div>
@endsection
