@extends('layouts.app')
@section('title', 'Utility rates')
@section('page-title', 'Utility rates')
@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="da-panel p-4">
                <h2 class="h6 mb-3">Add rate</h2>
                <form method="POST" action="{{ route('organization.utility-rates.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label">Property</label><select name="property_id" class="form-select" required>@foreach($properties as $p)<option value="{{ $p->id }}" @selected($propertyId===$p->id)>{{ $p->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Utility</label><select name="utility_type" class="form-select">@foreach(App\Enums\UtilityType::cases() as $t)<option value="{{ $t->value }}">{{ $t->label() }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Rate per unit (₱)</label><input type="number" step="0.0001" name="rate_per_unit" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Unit label</label><input type="text" name="unit_label" class="form-control" placeholder="kWh / cu.m"></div>
                    <div class="mb-3"><label class="form-label">Effective from</label><input type="date" name="effective_from" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div>
                    <button type="submit" class="btn btn-primary btn-sm">Save rate</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="da-panel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><x-da-table-num-th /><th>Property</th><th>Utility</th><th>Rate</th><th>Effective</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($rates as $rate)
                                <tr>
                                    <x-da-table-num-td :paginator="$rates" :row="$loop->index" />
                                    <td>{{ $rate->property->name }}</td>
                                    <td>{{ $rate->utility_type->label() }}</td>
                                    <td>₱{{ number_format($rate->rate_per_unit, 4) }} / {{ $rate->unit_label }}</td>
                                    <td class="small">{{ $rate->effective_from->format('M j, Y') }}@if($rate->effective_to) – {{ $rate->effective_to->format('M j, Y') }}@endif</td>
                                    <td><span class="badge text-bg-{{ $rate->is_active ? 'success' : 'secondary' }}">{{ $rate->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="da-empty-state">No rates yet for this property.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-da-pagination :paginator="$rates" />
            </div>
        </div>
    </div>
@endsection
