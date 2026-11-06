@extends('layouts.app')

@section('title', 'Team — '.config('app.name'))
@section('page-title', 'Team members')
@section('page-subtitle', 'Manage staff accounts and roles')

@section('page-actions')
    <a href="{{ route('organization.users.create') }}" class="btn btn-primary btn-sm">Add member</a>
@endsection

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <x-da-table-num-th />
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <x-da-table-num-td :paginator="$users" :row="$loop->index" />
                            <td>
                                <div class="fw-medium">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->account_type->label() }}</div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                            <td><span class="badge text-bg-light border">{{ $user->status->label() }}</span></td>
                            <td class="text-end">
                                @can('update', $user)
                                    <a href="{{ route('organization.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No team members yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$users" class="card-footer bg-white border-top" />
    </div>
@endsection
