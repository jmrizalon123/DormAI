@extends('layouts.app')
@section('title', 'Rental agreements')
@section('page-title', 'Rental agreements')
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><x-da-table-num-th /><th>Contract</th><th>Property</th><th>Period</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($contracts as $contract)
                        <tr>
                            <x-da-table-num-td :paginator="$contracts" :row="$loop->index" />
                            <td class="font-monospace small">{{ $contract->contract_number }}</td>
                            <td>{{ $contract->property->name }}</td>
                            <td class="small">{{ $contract->start_date->format('M j, Y') }} – {{ $contract->end_date?->format('M j, Y') ?? 'Open' }}</td>
                            <td>{{ $contract->status->label() }}</td>
                            <td class="text-end"><a href="{{ route('portal.contracts.show', $contract) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted p-3">No contracts on file.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$contracts" />
    </div>
@endsection
