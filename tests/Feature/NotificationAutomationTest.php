<?php

namespace Tests\Feature;

use App\Enums\UserAccountType;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\MaintenanceRequestSubmittedNotification;
use App\Services\Billing\InvoiceService;
use App\Services\Maintenance\MaintenanceService;
use App\Services\Notifications\AutomationNotificationService;
use Carbon\Carbon;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TenantSeeder::class);
    }

    public function test_issuing_invoice_notifies_tenant_user(): void
    {
        Notification::fake();

        [$organization, $tenant, $tenantUser] = $this->demoTenantContext();

        $invoice = app(InvoiceService::class)->createDraft(
            $organization,
            $tenant,
            [['description' => 'Rent', 'unit_price' => 1500]],
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );

        app(InvoiceService::class)->issue($invoice);

        Notification::assertSentTo($tenantUser, InvoiceIssuedNotification::class);
    }

    public function test_maintenance_request_notifies_staff(): void
    {
        Notification::fake();

        [, $tenant, $tenantUser] = $this->demoTenantContext();
        $owner = User::query()->where('email', 'owner@demo-dorm.test')->firstOrFail();

        app(MaintenanceService::class)->createRequestForTenant($tenant, $tenantUser, [
            'title' => 'Leaking faucet',
            'description' => 'Kitchen sink drip',
            'priority' => 'normal',
        ]);

        Notification::assertSentTo($owner, MaintenanceRequestSubmittedNotification::class);
    }

    public function test_payment_reminder_command_is_idempotent(): void
    {
        Notification::fake();

        $organization = Organization::factory()->create();
        $tenant = Tenant::factory()->create(['organization_id' => $organization->id]);
        $tenantUser = User::factory()->create([
            'organization_id' => $organization->id,
            'account_type' => UserAccountType::Tenant,
        ]);
        $tenant->update(['user_id' => $tenantUser->id]);

        $invoice = app(InvoiceService::class)->createDraft(
            $organization,
            $tenant,
            [['description' => 'Rent', 'unit_price' => 500]],
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );
        $invoice->update(['due_date' => now()->addDays(3)->toDateString()]);
        app(InvoiceService::class)->issue($invoice->fresh());

        $service = app(AutomationNotificationService::class);
        $first = $service->sendPaymentReminders($organization->id);
        $second = $service->sendPaymentReminders($organization->id);

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
    }

    public function test_notifications_index_requires_auth(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    /**
     * @return array{0: Organization, 1: Tenant, 2: User}
     */
    private function demoTenantContext(): array
    {
        $tenantUser = User::query()->where('email', 'tenant@demo-dorm.test')->firstOrFail();
        $tenant = Tenant::query()->where('user_id', $tenantUser->id)->firstOrFail();
        $organization = Organization::query()->findOrFail($tenant->organization_id);

        return [$organization, $tenant, $tenantUser];
    }
}
