<?php

namespace Tests\Feature;

use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Finance\ExpenseService;
use App\Services\Finance\FinancialSummaryService;
use Carbon\Carbon;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * @return array{organization: Organization, owner: User, accountant: User, category: ExpenseCategory}
     */
    private function financeUsers(): array
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        $accountant = User::factory()->create(['organization_id' => $organization->id]);

        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');
        $accountant->assignRole('accountant');

        app(ExpenseService::class)->seedDefaultCategories($organization);
        $category = ExpenseCategory::query()->where('organization_id', $organization->id)->firstOrFail();

        return compact('organization', 'owner', 'accountant', 'category');
    }

    public function test_expense_submit_approve_workflow(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'accountant' => $accountant, 'category' => $category] = $this->financeUsers();

        $expense = app(ExpenseService::class)->create($organization, $accountant, [
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'amount' => 1250,
            'description' => 'Internet bill',
        ]);

        app(ExpenseService::class)->submit($expense);
        $expense->refresh();
        $this->assertSame(ExpenseStatus::PendingApproval, $expense->status);

        app(ExpenseService::class)->approve($expense, $owner);
        $expense->refresh();
        $this->assertSame(ExpenseStatus::Approved, $expense->status);
        $this->assertSame($owner->id, $expense->approved_by);
    }

    public function test_user_cannot_approve_own_expense(): void
    {
        ['organization' => $organization, 'accountant' => $accountant, 'category' => $category] = $this->financeUsers();

        $expense = app(ExpenseService::class)->create($organization, $accountant, [
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'amount' => 500,
            'description' => 'Supplies',
        ]);
        app(ExpenseService::class)->submit($expense);

        $this->expectException(\InvalidArgumentException::class);
        app(ExpenseService::class)->approve($expense->fresh(), $accountant);
    }

    public function test_financial_summary_includes_payments_and_approved_expenses(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'accountant' => $accountant, 'category' => $category] = $this->financeUsers();

        $tenant = Tenant::factory()->create(['organization_id' => $organization->id]);

        Payment::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'payment_number' => 'PAY-TEST-001',
            'paid_at' => now(),
            'amount' => 5000,
            'payment_method' => PaymentMethod::Cash,
            'recorded_by' => $owner->id,
        ]);

        $expense = app(ExpenseService::class)->create($organization, $accountant, [
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'amount' => 800,
            'description' => 'Cleaning',
        ]);
        app(ExpenseService::class)->submit($expense);
        app(ExpenseService::class)->approve($expense->fresh(), $owner);

        $report = app(FinancialSummaryService::class)->profitAndLoss(
            $organization,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );

        $this->assertSame(5000.0, $report['income']);
        $this->assertSame(800.0, $report['expenses']);
        $this->assertSame(4200.0, $report['net']);
    }

    public function test_expense_routes_require_organization_scope(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'category' => $category] = $this->financeUsers();

        $otherOrg = Organization::factory()->create();
        $otherOwner = User::factory()->organizationOwner()->create(['organization_id' => $otherOrg->id]);
        setPermissionsTeamId($otherOrg->id);
        $otherOwner->assignRole('organization-owner');

        $expense = Expense::query()->create([
            'organization_id' => $organization->id,
            'expense_category_id' => $category->id,
            'recorded_by' => $owner->id,
            'expense_date' => now(),
            'amount' => 100,
            'description' => 'Secret',
            'status' => ExpenseStatus::Draft,
        ]);

        setPermissionsTeamId($otherOrg->id);
        $this->actingAs($otherOwner)
            ->get(route('organization.expenses.show', $expense))
            ->assertNotFound();
    }
}
