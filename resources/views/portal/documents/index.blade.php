@extends('layouts.app')
@section('title', 'Documents')
@section('page-title', 'My documents')
@section('content')
    <div class="da-panel">
        <ul class="list-group list-group-flush">
            @forelse($documents as $document)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div><span class="fw-semibold">{{ $document->original_name }}</span><div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $document->category) }}</div></div>
                    <a href="{{ route('portal.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">Download</a>
                </li>
            @empty
                <li class="list-group-item text-muted">No documents uploaded by your dorm yet.</li>
            @endforelse
        </ul>
    </div>
@endsection
