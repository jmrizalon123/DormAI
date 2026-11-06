@extends('layouts.app')
@section('title', 'Announcements')
@section('page-title', 'Tenant announcements')
@section('page-actions')@can('create', App\Models\Announcement::class)<a href="{{ route('organization.announcements.create') }}" class="btn btn-primary btn-sm">New announcement</a>@endcan@endsection
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><x-da-table-num-th /><th>Title</th><th>Published</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($announcements as $item)
                        <tr>
                            <x-da-table-num-td :paginator="$announcements" :row="$loop->index" />
                            <td>{{ $item->title }}</td>
                            <td class="small">{{ $item->published_at?->format('M j, Y') ?? '—' }}</td>
                            <td>@if($item->is_published)<span class="badge text-bg-success">Live</span>@else<span class="badge text-bg-secondary">Draft</span>@endif</td>
                            <td class="text-end"><a href="{{ route('organization.announcements.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted p-3">No announcements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$announcements" />
    </div>
@endsection
