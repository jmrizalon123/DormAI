@extends('layouts.app')
@section('title', $tenant->fullName())
@section('page-title', $tenant->fullName())
@section('page-subtitle', 'Tenant profile')
@section('page-actions')
    <a href="{{ route('organization.tenants.edit', $tenant) }}" class="btn btn-outline-secondary btn-sm">Edit profile</a>
@endsection
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-medium">Current assignment</div>
                <div class="card-body">
                    @if($tenant->activeAssignment)
                        <p class="mb-2">{{ $tenant->activeAssignment->property->name }} · {{ $tenant->activeAssignment->building->name }} · {{ $tenant->activeAssignment->floor->name }} · Room {{ $tenant->activeAssignment->room->room_number }} · Bed {{ $tenant->activeAssignment->bedSpace->label }}</p>
                        <p class="text-muted small mb-3">Move-in: {{ $tenant->activeAssignment->move_in_date->format('M j, Y') }}</p>
                        <form method="POST" action="{{ route('organization.tenants.assignments.destroy', [$tenant, $tenant->activeAssignment]) }}" class="row g-2">@csrf @method('DELETE')
                            <div class="col-md-4"><input type="date" name="move_out_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}"></div>
                            <div class="col-md-5"><input type="text" name="ended_reason" class="form-control form-control-sm" placeholder="Move-out reason (optional)"></div>
                            <div class="col-md-3"><button class="btn btn-sm btn-outline-danger w-100">Record move-out</button></div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('organization.tenants.assignments.store', $tenant) }}" class="row g-2">@csrf
                            <div class="col-md-6"><select name="bed_space_id" class="form-select" required><option value="">Select available bed</option>@foreach($availableBeds as $bed)<option value="{{ $bed->id }}">{{ $bed->room->property->name }} · Rm {{ $bed->room->room_number }} · Bed {{ $bed->label }}</option>@endforeach</select></div>
                            <div class="col-md-3"><input type="date" name="move_in_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div>
                            <div class="col-md-3"><button class="btn btn-primary w-100">Assign bed</button></div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-medium">Assignment history</div>
                <ul class="list-group list-group-flush">
                    @forelse($tenant->assignmentHistory as $entry)
                        <li class="list-group-item small">
                            <span class="fw-medium text-capitalize">{{ str_replace('_', ' ', $entry->event) }}</span>
                            · {{ $entry->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                            @if($entry->meta)<div class="text-muted">{{ json_encode($entry->meta) }}</div>@endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No history yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-medium">Emergency contacts</div>
                <div class="card-body">
                    @foreach($tenant->emergencyContacts as $contact)
                        <div class="d-flex justify-content-between border rounded p-2 mb-2">
                            <div><div class="fw-medium">{{ $contact->name }} @if($contact->is_primary)<span class="badge text-bg-light border">Primary</span>@endif</div><div class="small text-muted">{{ $contact->relationship }} · {{ $contact->phone }}</div></div>
                            <form method="POST" action="{{ route('organization.tenants.emergency-contacts.destroy', [$tenant, $contact]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form>
                        </div>
                    @endforeach
                    <form method="POST" action="{{ route('organization.tenants.emergency-contacts.store', $tenant) }}" class="row g-2">@csrf
                        <div class="col-md-4"><input name="name" class="form-control form-control-sm" placeholder="Name" required></div>
                        <div class="col-md-3"><input name="relationship" class="form-control form-control-sm" placeholder="Relationship"></div>
                        <div class="col-md-3"><input name="phone" class="form-control form-control-sm" placeholder="Phone" required></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Add</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4"><div class="card-body">
                <h2 class="h6 text-uppercase text-muted">Tenant portal</h2>
                @if($tenant->hasPortalAccess())
                    <p class="small mb-0">Portal enabled for <strong>{{ $tenant->user?->email }}</strong>. Tenant signs in with the same login page as staff.</p>
                @else
                    @can('update', $tenant)
                        <p class="small text-muted">Enable login so this tenant can view bills, submit payments, and report maintenance.</p>
                        <form method="POST" action="{{ route('organization.tenants.portal-access.store', $tenant) }}" class="row g-2">@csrf
                            <div class="col-12"><input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $tenant->email) }}" placeholder="Login email" required></div>
                            <div class="col-12"><input type="password" name="password" class="form-control form-control-sm" placeholder="Temporary password" required minlength="8"></div>
                            <div class="col-12"><input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirm password" required></div>
                            <div class="col-12"><button class="btn btn-sm btn-primary w-100">Enable portal access</button></div>
                        </form>
                    @endcan
                @endif
            </div></div>

            <div class="card border-0 shadow-sm mb-4"><div class="card-body">
                <h2 class="h6 text-uppercase text-muted">Profile</h2>
                <dl class="small mb-0">
                    <dt>Status</dt><dd><span class="badge text-bg-{{ $tenant->status->badgeClass() }}">{{ $tenant->status->label() }}</span></dd>
                    <dt>Phone</dt><dd>{{ $tenant->phone ?: '—' }}</dd>
                    <dt>Email</dt><dd>{{ $tenant->email ?: '—' }}</dd>
                    <dt>Valid ID</dt><dd>{{ collect([$tenant->valid_id_type, $tenant->valid_id_number])->filter()->join(' · ') ?: '—' }}</dd>
                    <dt>Address</dt><dd>{{ collect([$tenant->address_line1, $tenant->city, $tenant->province])->filter()->join(', ') ?: '—' }}</dd>
                </dl>
            </div></div>

            <div class="card border-0 shadow-sm"><div class="card-body">
                <h2 class="h6 text-uppercase text-muted">Documents (private)</h2>
                @foreach($tenant->documents as $document)
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <a href="{{ route('organization.tenants.documents.show', [$tenant, $document]) }}">{{ $document->original_name }}</a>
                        <form method="POST" action="{{ route('organization.tenants.documents.destroy', [$tenant, $document]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">×</button></form>
                    </div>
                @endforeach
                <form method="POST" action="{{ route('organization.tenants.documents.store', $tenant) }}" enctype="multipart/form-data" class="mt-2">@csrf
                    <select name="category" class="form-select form-select-sm mb-2"><option value="valid_id">Valid ID</option><option value="contract">Contract</option><option value="other">Other</option></select>
                    <input type="file" name="document" class="form-control form-control-sm mb-2" required>
                    <button class="btn btn-sm btn-primary">Upload</button>
                </form>
            </div></div>
        </div>
    </div>
@endsection
