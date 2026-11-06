<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\PaymentService;
use Carbon\Carbon;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BillingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function billingOwner(): array
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        $tenant = Tenant::factory()->create(['organization_id' => $organization->id]);

        return compact('organization', 'owner', 'tenant');
    }

    public function test_manual_invoice_issue_and_full_payment(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'tenant' => $tenant] = $this->billingOwner();

        $invoice = app(InvoiceService::class)->createDraft(
            $organization,
            $tenant,
            [['description' => 'Rent', 'unit_price' => 3500]],
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );

        app(InvoiceService::class)->issue($invoice);

        $payment = app(PaymentService::class)->record(
            $organization,
            $tenant,
            3500,
            PaymentMethod::Cash,
            [['invoice_id' => $invoice->id, 'amount' => 3500]],
            $owner,
        );

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertSame('0.00', $invoice->balance);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 3500]);
    }

    public function test_partial_payment_updates_invoice_status(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'tenant' => $tenant] = $this->billingOwner();

        $invoice = app(InvoiceService::class)->createDraft(
            $organization,
            $tenant,
            [['description' => 'Rent', 'unit_price' => 4000]],
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );
        app(InvoiceService::class)->issue($invoice);

        app(PaymentService::class)->record(
            $organization,
            $tenant,
            1500,
            PaymentMethod::Gcash,
            [['invoice_id' => $invoice->id, 'amount' => 1500]],
            $owner,
        );

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertSame('2500.00', $invoice->balance);
    }

    public function test_monthly_generation_creates_invoice_from_active_contract(): void
    {
        ['organization' => $organization, 'tenant' => $tenant] = $this->billingOwner();

        $property = Property::factory()->create(['organization_id' => $organization->id]);
        $building = Building::factory()->create(['organization_id' => $organization->id, 'property_id' => $property->id]);
        $floor = Floor::factory()->create(['organization_id' => $organization->id, 'building_id' => $building->id]);
        $room = Room::factory()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);
        $bed = BedSpace::factory()->create(['organization_id' => $organization->id, 'room_id' => $room->id]);

        Contract::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'bed_space_id' => $bed->id,
            'contract_number' => 'CNT-TEST-001',
            'start_date' => now()->subMonth()->startOfMonth(),
            'monthly_rate' => 3200,
            'security_deposit' => 0,
            'advance_payment' => 0,
            'terms' => 'Test',
            'status' => ContractStatus::Active,
        ]);

        $created = app(InvoiceService::class)->generateMonthlyInvoices($organization, now());

        $this->assertCount(1, $created);
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $tenant->id,
            'status' => InvoiceStatus::Issued->value,
        ]);
    }

    public function test_owner_cannot_view_invoice_from_other_organization(): void
    {
        ['owner' => $owner] = $this->billingOwner();
        $other = Invoice::query()->create([
            'organization_id' => Organization::factory()->create()->id,
            'tenant_id' => Tenant::factory()->create()->id,
            'invoice_number' => 'INV-X-001',
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'issue_date' => now(),
            'due_date' => now()->addDays(5),
            'subtotal' => 1000,
            'balance' => 1000,
            'status' => InvoiceStatus::Issued,
        ]);

        $this->actingAs($owner)->get(route('organization.invoices.show', $other))->assertNotFound();
    }

    public function test_mark_overdue_command(): void
    {
        ['organization' => $organization, 'tenant' => $tenant] = $this->billingOwner();

        $invoice = app(InvoiceService::class)->createDraft(
            $organization,
            $tenant,
            [['description' => 'Rent', 'unit_price' => 2000]],
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );
        app(InvoiceService::class)->issue($invoice);
        $invoice->update(['due_date' => now()->subDays(10)]);

        Artisan::call('dormai:mark-overdue-invoices');

        $this->assertSame(InvoiceStatus::Overdue, $invoice->fresh()->status);
    }
}
