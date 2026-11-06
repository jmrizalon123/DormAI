<?php

namespace App\Support\Tenancy;

use App\Models\Organization;
use Illuminate\Support\Facades\Cache;

class PlatformOrganization
{
    public static function id(): int
    {
        if (app()->environment('testing')) {
            return self::resolveId();
        }

        return Cache::rememberForever('dormai.platform_organization_id', fn () => self::resolveId());
    }

    private static function resolveId(): int
    {
        $organization = Organization::query()
            ->where('slug', config('dormai.platform_organization_slug'))
            ->firstOrFail();

        return (int) $organization->id;
    }

    public static function isPlatformOrganization(?Organization $organization): bool
    {
        return $organization !== null
            && $organization->slug === config('dormai.platform_organization_slug');
    }
}
