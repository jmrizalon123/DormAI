@extends('layouts.app')

@section('title', 'Contact messages — '.config('app.name'))
@section('page-title', 'Contact messages')
@section('page-subtitle', 'Marketplace inquiries from the public contact form')

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, name, email, subject">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach (['pending', 'read', 'replied', 'closed'] as $status)
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
                        <th>From</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Replies</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $contact)
                        <tr>
                            <x-da-table-num-td :paginator="$messages" :row="$loop->index" />
                            <td>
                                <div class="fw-medium">{{ $contact->name }}</div>
                                <div class="small text-muted">{{ $contact->email }}</div>
                                <div class="small text-muted">{{ $contact->reference }}</div>
                            </td>
                            <td>{{ Str::limit($contact->subject, 48) }}</td>
                            <td><span class="badge text-bg-{{ $contact->status->badgeClass() }}">{{ $contact->status->label() }}</span></td>
                            <td>{{ $contact->replies_count }}</td>
                            <td class="small text-muted">{{ $contact->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('platform.contact-messages.show', $contact) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No contact messages yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="card-footer bg-white">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
