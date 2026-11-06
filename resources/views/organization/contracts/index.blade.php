@extends('layouts.app')
@section('title', 'Contracts — '.config('app.name'))
@section('page-title', 'Contracts')
@section('page-subtitle', 'Active and historical rental agreements')
@section('page-actions')
    <a href="{{ route('organization.contract-templates.index') }}" class="btn btn-outline-secondary btn-sm">Templates</a>
@endsection
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Contract</th><th>Tenant</th><th>Unit</th><th>Term</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($contracts as $contract)
                        <tr>
                            <x-da-table-num-td :paginator="$contracts" :row="$loop->index" />
                            <td class="fw-semibold font-monospace small">{{ $contract->contract_number }}</td>
                            <td>{{ $contract->tenant->fullName() }}</td>
                            <td class="small">{{ $contract->property->name }} · {{ $contract->room->room_number }} · {{ $contract->bedSpace->label }}</td>
                            <td class="small">{{ $contract->start_date->format('M j, Y') }} — {{ $contract->end_date?->format('M j, Y') ?: 'Open' }}</td>
                            <td><span class="badge text-bg-secondary">{{ $contract->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.contracts.show', $contract) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No contracts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$contracts" />
    </div>
@endsection
