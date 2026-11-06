@extends('layouts.app')

@section('title', 'Notifications — '.config('app.name'))
@section('page-title', 'Notifications')
@section('page-subtitle', 'In-app alerts for reservations, billing, maintenance, and agreements')

@section('page-actions')
    @if(auth()->user()->unreadNotifications->count())
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Mark all read</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="da-panel">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = $notification->read_at === null;
            @endphp
            <a
                href="{{ route('notifications.read', $notification->id) }}"
                class="d-block text-decoration-none border-bottom px-3 px-lg-4 py-3 {{ $isUnread ? 'bg-opacity-10' : '' }}"
                style="{{ $isUnread ? 'background: var(--da-accent-soft);' : '' }}"
            >
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <span class="text-muted small da-table-num flex-shrink-0 pt-1">{{ $notifications->firstItem() + $loop->index }}</span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="fw-semibold {{ $isUnread ? 'text-body' : 'text-muted' }}">{{ $data['title'] ?? 'Notification' }}</div>
                        <div class="small {{ $isUnread ? 'text-body' : 'text-muted' }} mb-1">{{ $data['message'] ?? '' }}</div>
                        <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    @if($isUnread)
                        <span class="badge rounded-pill text-bg-primary flex-shrink-0">New</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="da-empty-state">
                <i class="bi bi-bell"></i>
                <p class="mb-0">No notifications yet.</p>
            </div>
        @endforelse

        <x-da-pagination :paginator="$notifications" />
    </div>
@endsection
