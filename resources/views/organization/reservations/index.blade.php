@extends('layouts.app')
@section('title', 'Reservations — '.config('app.name'))
@section('page-title', 'Reservations')
@section('page-subtitle', 'Review public booking requests and check-ins')
@section('content')
    <div class="da-filter-bar">
        <form method="GET" class="row g-3 da-filter-row">
            <div class="col-md-5"><label class="form-label">Search</label><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, name, email"></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(App\Enums\ReservationStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-md-3 da-filter-submit"><button type="submit" class="btn btn-primary w-100">Apply filters</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Reference</th><th>Applicant</th><th>Bed</th><th>Check-in</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        <tr>
                            <x-da-table-num-td :paginator="$reservations" :row="$loop->index" />
                            <td class="fw-semibold font-monospace small">{{ $reservation->reference_code }}</td>
                            <td><div class="fw-medium">{{ $reservation->applicantFullName() }}</div><div class="small text-muted">{{ $reservation->applicant_email }}</div></td>
                            <td class="small">{{ $reservation->property->name }} · {{ $reservation->room->room_number }} · {{ $reservation->bedSpace->label }}</td>
                            <td>{{ $reservation->requested_check_in->format('M j, Y') }}</td>
                            <td><span class="badge text-bg-{{ $reservation->status->badgeClass() }}">{{ $reservation->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="da-empty-state"><i class="bi bi-calendar-x"></i>No reservations yet.<div class="small mt-1">Public booking requests will appear here.</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$reservations" />
    </div>
@endsection
