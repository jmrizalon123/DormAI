<?php

namespace App\Support\View;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class BackNavigation
{
    /**
     * @return array{url: ?string, label: string}
     */
    public static function resolve(?string $overrideUrl = null, ?string $overrideLabel = null): array
    {
        $label = $overrideLabel ?: 'Back';

        if ($overrideUrl !== null && $overrideUrl !== '') {
            return ['url' => $overrideUrl, 'label' => $label];
        }

        $routeName = Route::currentRouteName();

        if ($routeName === null || self::isDashboardRoute($routeName)) {
            return ['url' => null, 'label' => $label];
        }

        if ($routeName === 'profile.edit') {
            return ['url' => route('dashboard'), 'label' => $label];
        }

        if (str_ends_with($routeName, '.index')) {
            return ['url' => self::contextDashboardUrl($routeName), 'label' => $label];
        }

        $indexUrl = self::resolveIndexRouteUrl($routeName);
        if ($indexUrl !== null) {
            return ['url' => $indexUrl, 'label' => $label];
        }

        $previous = url()->previous();
        if (self::isSafePreviousUrl($previous)) {
            return ['url' => $previous, 'label' => $label];
        }

        return ['url' => self::contextDashboardUrl($routeName), 'label' => $label];
    }

    private static function isDashboardRoute(string $routeName): bool
    {
        return in_array($routeName, [
            'dashboard',
            'home',
            'organization.dashboard',
            'portal.dashboard',
            'platform.dashboard',
        ], true);
    }

    private static function contextDashboardUrl(string $routeName): string
    {
        if (str_starts_with($routeName, 'portal.')) {
            return route('portal.dashboard');
        }

        if (str_starts_with($routeName, 'platform.')) {
            return route('platform.dashboard');
        }

        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return route('platform.dashboard');
        }

        if (Auth::check() && Auth::user()->isTenant()) {
            return route('portal.dashboard');
        }

        return route('organization.dashboard');
    }

    private static function resolveIndexRouteUrl(string $routeName): ?string
    {
        $parts = explode('.', $routeName);

        while (count($parts) > 1) {
            array_pop($parts);
            $candidate = implode('.', $parts).'.index';

            if (Route::has($candidate)) {
                return route($candidate);
            }
        }

        return null;
    }

    private static function isSafePreviousUrl(string $url): bool
    {
        if ($url === '' || $url === url()->current()) {
            return false;
        }

        $previousHost = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url(url('/'), PHP_URL_HOST);

        if ($previousHost !== null && $appHost !== null && $previousHost !== $appHost) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '';

        foreach (['/login', '/register', '/logout', '/forgot-password'] as $blocked) {
            if (str_starts_with($path, $blocked)) {
                return false;
            }
        }

        return true;
    }
}
