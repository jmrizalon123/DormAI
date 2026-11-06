@extends('layouts.app')
@section('title', 'Payment submissions')
@section('page-title', 'Payment submissions')
@section('page-actions')<a href="{{ route('portal.payment-submissions.create') }}" class="btn btn-primary btn-sm">New submission</a>@endsection
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><x-da-table-num-th /><th>Submitted</th><th>Reference</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($submissions as $item)
                        <tr>
                            <x-da-table-num-td :paginator="$submissions" :row="$loop->index" />
                            <td class="small">{{ $item->created_at->format('M j, Y') }}</td>
                            <td>{{ $item->reference_number }} · {{ $item->payment_method }}</td>
                            <td>₱{{ number_format($item->amount, 2) }}</td>
                            <td><span class="badge text-bg-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted p-3">No submissions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$submissions" />
    </div>
@endsection
