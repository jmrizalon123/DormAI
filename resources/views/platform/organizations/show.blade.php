@extends('layouts.app')

@section('title', $organization->name.' — '.config('app.name'))

@section('page-title', $organization->name)
@section('page-subtitle', 'Organization review')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge text-bg-{{ $organization->status->badgeClass() }}">{{ $organization->status->label() }}</span>
                            <div class="small text-muted mt-2">Slug: {{ $organization->slug }}</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @can('approve', $organization)
                                <form method="POST" action="{{ route('platform.organizations.approve', $organization) }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm">Approve</button>
                                </form>
                            @endcan
                            @can('reject', $organization)
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#rejectForm">Reject</button>
                            @endcan
                            @can('suspend', $organization)
                                <form method="POST" action="{{ route('platform.organizations.suspend', $organization) }}">
                                    @csrf
                                    <button class="btn btn-outline-secondary btn-sm">Suspend</button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    @can('reject', $organization)
                        <div class="collapse mb-3" id="rejectForm">
                            <form method="POST" action="{{ route('platform.organizations.reject', $organization) }}" class="border rounded p-3 bg-light">
                                @csrf
                                <label class="form-label">Rejection reason</label>
                                <textarea name="rejection_reason" class="form-control mb-2" rows="3" required></textarea>
                                <button class="btn btn-danger btn-sm">Confirm rejection</button>
                            </form>
                        </div>
                    @endcan

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $organization->email }}</dd>
                        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $organization->phone ?: '—' }}</dd>
                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9">
                            {{ collect([$organization->address_line1, $organization->city, $organization->province, $organization->postal_code])->filter()->join(', ') ?: '—' }}
                        </dd>
                        @if ($organization->rejection_reason)
                            <dt class="col-sm-3">Notes</dt><dd class="col-sm-9">{{ $organization->rejection_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-medium">Users</div>
                <ul class="list-group list-group-flush">
                    @forelse ($organization->users as $user)
                        <li class="list-group-item">
                            <div class="fw-medium">{{ $user->name }}</div>
                            <div class="small text-muted">{{ $user->email }} · {{ $user->account_type->label() }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No users linked.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
