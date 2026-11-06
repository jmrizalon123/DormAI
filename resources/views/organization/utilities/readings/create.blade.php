@extends('layouts.app')
@section('title', 'Record reading')
@section('page-title', 'Record utility reading')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.utility-readings.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Meter</label><select name="utility_meter_id" class="form-select" required>@foreach($meters as $m)<option value="{{ $m->id }}">{{ $m->label }} ({{ $m->utility_type->label() }} · {{ $m->property->name }})</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Reading date</label><input type="date" name="reading_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label">Current reading</label><input type="number" step="0.001" min="0" name="current_reading" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            <button type="submit" class="btn btn-primary">Save reading</button>
        </form>
    </div>
@endsection
