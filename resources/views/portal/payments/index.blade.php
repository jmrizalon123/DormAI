@extends('layouts.app')
@section('title', 'Payment history')
@section('page-title', 'Payment history')
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><x-da-table-num-th /><th>Date</th><th>Reference</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <x-da-table-num-td :paginator="$payments" :row="$loop->index" />
                            <td>{{ $payment->paid_at->format('M j, Y') }}</td>
                            <td class="font-monospace small">{{ $payment->payment_number }}</td>
                            <td>{{ $payment->payment_method->label() }}</td>
                            <td class="text-end fw-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted p-3">No recorded payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$payments" />
    </div>
@endsection
