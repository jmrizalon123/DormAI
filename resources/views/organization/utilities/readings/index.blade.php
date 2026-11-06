@extends('layouts.app')
@section('title', 'Utility readings')
@section('page-title', 'Utility readings')
@section('page-actions')<a href="{{ route('organization.utility-readings.create') }}" class="btn btn-primary btn-sm">Record reading</a>@endsection
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Date</th><th>Meter</th><th>Previous</th><th>Current</th><th>Consumption</th><th></th></tr></thead>
                <tbody>
                    @forelse($readings as $reading)
                        <tr>
                            <x-da-table-num-td :paginator="$readings" :row="$loop->index" />
                            <td>{{ $reading->reading_date->format('M j, Y') }}</td>
                            <td><div class="fw-semibold">{{ $reading->meter->label }}</div><div class="small text-muted">{{ $reading->meter->utility_type->label() }} · {{ $reading->meter->property->name }}</div></td>
                            <td>{{ number_format($reading->previous_reading, 3) }}</td>
                            <td>{{ number_format($reading->current_reading, 3) }}</td>
                            <td class="fw-semibold">{{ number_format($reading->consumption, 3) }}</td>
                            <td class="text-end"><a href="{{ route('organization.utility-readings.show', $reading) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="da-empty-state">No readings yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$readings" />
    </div>
@endsection
