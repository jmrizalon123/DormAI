<?php

namespace App\Support\Routing;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\Support\Facades\Route;

class PublicOrganizationBinding
{
    public static function register(): void
    {
        Route::pattern('publicOrganization', '[a-z0-9\-]+');
    }

    public static function resolve(string $slug): Organization
    {
        $platformSlug = (string) config('dormai.platform_organization_slug');

        return Organization::query()
            ->where('slug', $slug)
            ->where('status', OrganizationStatus::Active)
            ->when($platformSlug !== '', fn ($query) => $query->where('slug', '!=', $platformSlug))
            ->firstOrFail();
    }
}
