@php
    $user = auth()->user();
    $dashboardUrl = route('dashboard');
@endphp

<div class="da-sidebar-brand-block border-bottom border-secondary border-opacity-25">
    <a href="{{ $dashboardUrl }}" class="da-sidebar-brand-link">
        <x-brand.dormai-logo variant="full" class="da-sidebar-logo" />
        @if($user?->organization && ! $user->isSuperAdmin())
            <span class="da-sidebar-org da-sidebar-label">{{ $user->organization->name }}</span>
        @endif
    </a>
</div>
