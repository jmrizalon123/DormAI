@php
    $user = auth()->user();
    $profileRoute = $user?->isTenant() ? route('portal.profile.edit') : route('profile.edit');
    $securityUrl = route('profile.edit').'#account-security';
    $initials = collect(preg_split('/\s+/', trim($user->name ?? 'U')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->join('');
@endphp

<div class="da-sidebar-footer mt-auto border-top border-secondary border-opacity-25">
    <div class="da-sidebar-flyout-anchor">
        <a href="{{ $profileRoute }}" class="da-sidebar-user-card text-decoration-none">
            <span class="da-sidebar-user-avatar" aria-hidden="true">{{ $initials ?: 'U' }}</span>
            <span class="da-sidebar-user-meta">
                <span class="da-sidebar-user-name">{{ $user->name }}</span>
                <span class="da-sidebar-user-email">{{ $user->email }}</span>
            </span>
        </a>
        <div class="da-sidebar-flyout-panel da-sidebar-flyout-panel--profile" role="tooltip">
            <p class="da-sidebar-flyout-profile-name">{{ $user->name }}</p>
            <p class="da-sidebar-flyout-profile-email">{{ $user->email }}</p>
        </div>
    </div>

    <nav class="da-sidebar-footer-nav" aria-label="Account">
        <div class="da-sidebar-flyout-anchor">
            <button
                type="button"
                class="da-sidebar-footer-link"
                id="daThemeToggle"
                aria-pressed="false"
            >
                <i class="bi bi-brightness-high da-sidebar-footer-icon" aria-hidden="true"></i>
                <span class="da-sidebar-footer-text" data-theme-label>Dark mode</span>
            </button>
            <div class="da-sidebar-flyout-panel" role="tooltip">
                <span class="da-sidebar-flyout-action" data-theme-label>Dark mode</span>
            </div>
        </div>

        <div class="da-sidebar-flyout-anchor">
            <a href="{{ $securityUrl }}" class="da-sidebar-footer-link">
                <i class="bi bi-shield-check da-sidebar-footer-icon" aria-hidden="true"></i>
                <span class="da-sidebar-footer-text">Account security</span>
            </a>
            <div class="da-sidebar-flyout-panel" role="tooltip">
                <span class="da-sidebar-flyout-action">Account security</span>
            </div>
        </div>

        <div class="da-sidebar-flyout-anchor">
            <form method="POST" action="{{ route('logout') }}" class="m-0" data-da-confirm="Sign out of your account?" data-da-confirm-title="Sign out">
                @csrf
                <button type="submit" class="da-sidebar-footer-link">
                    <i class="bi bi-box-arrow-right da-sidebar-footer-icon" aria-hidden="true"></i>
                    <span class="da-sidebar-footer-text">Sign out</span>
                </button>
            </form>
            <div class="da-sidebar-flyout-panel" role="tooltip">
                <span class="da-sidebar-flyout-action">Sign out</span>
            </div>
        </div>
    </nav>
</div>
