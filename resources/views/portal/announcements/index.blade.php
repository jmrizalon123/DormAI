@extends('layouts.app')
@section('title', 'Announcements')
@section('page-title', 'Announcements')
@section('content')
    <div class="list-group da-panel">
        @forelse($announcements as $item)
            <a href="{{ route('portal.announcements.show', $item) }}" class="list-group-item list-group-item-action">
                <div class="fw-semibold">{{ $item->title }}</div>
                <div class="small text-muted">{{ $item->published_at?->format('M j, Y') }}</div>
            </a>
        @empty
            <div class="p-4 text-muted">No announcements published.</div>
        @endforelse
    </div>
    <x-da-pagination :paginator="$announcements" class="da-panel-footer mt-3 border rounded-bottom" />
@endsection
