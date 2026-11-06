<?php

namespace Tests\Unit;

use App\Support\View\BackNavigation;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BackNavigationTest extends TestCase
{
    public function test_dashboard_routes_hide_back_link(): void
    {
        Route::get('/test-org-dashboard', fn () => '')->name('organization.dashboard');

        $this->get('/test-org-dashboard');

        $navigation = BackNavigation::resolve();

        $this->assertNull($navigation['url']);
    }
}
