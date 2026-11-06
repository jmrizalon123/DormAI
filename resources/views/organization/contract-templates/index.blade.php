@extends('layouts.app')
@section('title', 'Contract templates')
@section('page-title', 'Contract templates')
@section('page-actions')<a href="{{ route('organization.contract-templates.create') }}" class="btn btn-primary btn-sm">New template</a>@endsection
@section('content')
    <div class="list-group shadow-sm">
        @forelse($templates as $template)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-medium">{{ $template->name }}</span>
                    @if($template->is_default)<span class="badge text-bg-primary ms-2">Default</span>@endif
                </div>
                <a href="{{ route('organization.contract-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
            </div>
        @empty
            <div class="list-group-item text-muted">No templates yet.</div>
        @endforelse
    </div>
@endsection
