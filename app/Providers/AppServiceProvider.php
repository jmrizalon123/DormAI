<?php

namespace App\Providers;

use App\Listeners\RecordFailedLogin;
use App\Listeners\RecordSuccessfulLogin;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Services\Ai\ListingAssistantService;
use App\Services\Ai\ListingCompanionAiClient;
use App\Support\Platform\PlatformListingContentSettings;
use App\Support\Routing\OrganizationScopedBindings;
use App\Support\Routing\PortalScopedBindings;
use App\Support\Routing\PublicOrganizationBinding;
use App\Support\Tenancy\OrganizationContext;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrganizationContext::class);
        $this->app->singleton(TenantPortalContext::class);
        $this->app->singleton(ListingCompanionAiClient::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::defaultView('vendor.pagination.dormai');

        OrganizationScopedBindings::register();
        PortalScopedBindings::register();
        PublicOrganizationBinding::register();

        Event::listen(Login::class, RecordSuccessfulLogin::class);
        Event::listen(Failed::class, RecordFailedLogin::class);

        View::composer('layouts.marketplace', function ($view): void {
            $assistant = app(ListingAssistantService::class);
            $view->with('marketplaceListingContent', PlatformListingContentSettings::defaults());
            $view->with('listingAssistantEnabled', $assistant->isEnabled());
            $view->with('listingAiCompanionEnabled', $assistant->isAiCompanionEnabled());
        });

        View::composer('public.listings.show', function ($view): void {
            $assistant = app(ListingAssistantService::class);
            $view->with('listingAssistantEnabled', $assistant->isEnabled());
            $view->with('listingAiCompanionEnabled', $assistant->isAiCompanionEnabled());
        });

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $organizationPendingReservationCount = 0;
            if ($user !== null && $user->organization_id !== null && ! $user->isTenant()) {
                $organizationPendingReservationCount = Reservation::query()
                    ->where('organization_id', $user->organization_id)
                    ->where('status', ReservationStatus::Pending)
                    ->count();
            }
            $view->with('organizationPendingReservationCount', $organizationPendingReservationCount);
        });
    }
}
