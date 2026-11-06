<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentSubmissionStatus;
use App\Enums\TenantStatus;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantPaymentSubmission;
use App\Models\User;
use App\Services\Portal\TenantPortalAccessService;
use App\Services\Tenant\TenantAssignmentService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class, EnsureEmailIsVerified::class]);
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * @return array{organization: Organization, tenant: Tenant, tenantUser: User, owner: User, property: Property}
     */
    private function portalFixture(): array
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        $property = Property::factory()->create(['organization_id' => $organization->id]);

        $tenant = Tenant::factory()->create([
            'organization_id' => $organization->id,
            'status' => TenantStatus::Active,
        ]);

        $building = Building::factory()->create(['organization_id' => $organization->id, 'property_id' => $property->id]);
        $floor = Floor::factory()->create(['organization_id' => $organization->id, 'building_id' => $building->id]);
        $room = Room::factory()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);
        $bed = BedSpace::factory()->create(['organization_id' => $organization->id, 'room_id' => $room->id]);

        app(TenantAssignmentService::class)->assign($tenant, $bed, now()->toDateString());

        $tenantUser = app(TenantPortalAccessService::class)->enable(
            $tenant->fresh(),
            'tenant-portal@test.test',
            'password123',
        );

        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        return compact('organization', 'tenant', 'tenantUser', 'owner', 'property');
    }

    public function test_tenant_dashboard_and_staff_route_isolation(): void
    {
        ['tenantUser' => $tenantUser, 'owner' => $owner] = $this->portalFixture();

        $this->actingAs($tenantUser)->get(route('portal.dashboard'))->assertOk();
        $this->actingAs($tenantUser)->get(route('organization.dashboard'))->assertForbidden();

        setPermissionsTeamId($owner->organization_id);
        $this->actingAs($owner)->get(route('organization.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('portal.dashboard'))->assertForbidden();
    }

    public function test_tenant_sees_only_own_invoices(): void
    {
        ['organization' => $organization, 'tenant' => $tenant, 'tenantUser' => $tenantUser, 'property' => $property] = $this->portalFixture();

        $mine = Invoice::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'invoice_number' => 'INV-00001',
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => 3500,
            'amount_paid' => 0,
            'balance' => 3500,
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
        ]);

        $otherTenant = Tenant::factory()->create(['organization_id' => $organization->id]);
        $otherInvoice = Invoice::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $otherTenant->id,
            'property_id' => $property->id,
            'invoice_number' => 'INV-00002',
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => 4000,
            'amount_paid' => 0,
            'balance' => 4000,
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
        ]);

        $this->actingAs($tenantUser)->get(route('portal.invoices.show', $mine))->assertOk();
        $this->actingAs($tenantUser)->get(route('portal.invoices.show', $otherInvoice))->assertNotFound();
    }

    public function test_tenant_can_submit_maintenance_and_payment_reference(): void
    {
        ['tenantUser' => $tenantUser] = $this->portalFixture();

        $this->actingAs($tenantUser)
            ->post(route('portal.maintenance.store'), [
                'title' => 'Broken light',
                'description' => 'Ceiling light flickers',
                'priority' => 'normal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('maintenance_requests', [
            'title' => 'Broken light',
        ]);

        $this->actingAs($tenantUser)
            ->post(route('portal.payment-submissions.store'), [
                'amount' => 500,
                'payment_method' => 'gcash',
                'reference_number' => 'GCASH123456',
            ])
            ->assertRedirect(route('portal.payment-submissions.index'));

        $this->assertDatabaseHas('tenant_payment_submissions', [
            'reference_number' => 'GCASH123456',
            'status' => PaymentSubmissionStatus::Pending->value,
        ]);
    }

    public function test_cashier_can_approve_payment_submission(): void
    {
        ['organization' => $organization, 'tenant' => $tenant, 'tenantUser' => $tenantUser, 'property' => $property] = $this->portalFixture();

        $cashier = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $cashier->assignRole('cashier');

        $invoice = Invoice::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'invoice_number' => 'INV-PORT-01',
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => 1000,
            'amount_paid' => 0,
            'balance' => 1000,
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
        ]);

        $this->actingAs($tenantUser)->post(route('portal.payment-submissions.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'payment_method' => 'gcash',
            'reference_number' => 'REF999',
        ]);

        $submission = TenantPaymentSubmission::query()->where('reference_number', 'REF999')->first();
        $this->assertNotNull($submission);

        setPermissionsTeamId($organization->id);
        $this->actingAs($cashier)
            ->post(route('organization.payment-submissions.approve', $submission))
            ->assertRedirect();

        $this->assertSame(PaymentSubmissionStatus::Approved, $submission->fresh()->status);
        $this->assertDatabaseHas('payments', ['tenant_id' => $tenant->id]);
    }
}
