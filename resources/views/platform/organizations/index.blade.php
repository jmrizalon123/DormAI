@extends('layouts.app')

@section('title', 'Organizations — '.config('app.name'))

@section('page-title', 'Organizations')
@section('page-subtitle', 'Review and manage registered dormitory businesses')

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email, slug">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach (['pending', 'active', 'suspended', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <x-da-table-num-th />
                        <th>Organization</th>
                        <th>Status</th>
                        <th>Users</th>
                        <th>Registered</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organizations as $organization)
                        <tr>
                            <x-da-table-num-td :paginator="$organizations" :row="$loop->index" />
                            <td>
                                <div class="fw-medium">{{ $organization->name }}</div>
                                <div class="small text-muted">{{ $organization->email }}</div>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $organization->status->badgeClass() }}">
                                    {{ $organization->status->label() }}
                                </span>
                            </td>
                            <td>{{ $organization->users_count }}</td>
                            <td>{{ $organization->created_at->timezone(config('app.timezone'))->format('M j, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('platform.organizations.show', $organization) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No organizations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$organizations" class="card-footer bg-white border-top" />
    </div>
@endsection
