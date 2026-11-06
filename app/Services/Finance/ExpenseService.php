<?php

namespace App\Services\Finance;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Organization $organization, User $recorder, array $data): Expense
    {
        $category = ExpenseCategory::query()
            ->where('organization_id', $organization->id)
            ->whereKey($data['expense_category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return Expense::query()->create([
            'organization_id' => $organization->id,
            'expense_category_id' => $category->id,
            'property_id' => $data['property_id'] ?? null,
            'recorded_by' => $recorder->id,
            'expense_date' => $data['expense_date'],
            'amount' => $data['amount'],
            'vendor' => $data['vendor'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'description' => $data['description'],
            'notes' => $data['notes'] ?? null,
            'status' => ExpenseStatus::Draft,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Expense $expense, array $data): Expense
    {
        if (! $expense->isEditable()) {
            throw new InvalidArgumentException('This expense cannot be edited.');
        }

        if (isset($data['expense_category_id'])) {
            ExpenseCategory::query()
                ->where('organization_id', $expense->organization_id)
                ->whereKey($data['expense_category_id'])
                ->where('is_active', true)
                ->firstOrFail();
        }

        $expense->fill([
            'expense_category_id' => $data['expense_category_id'] ?? $expense->expense_category_id,
            'property_id' => array_key_exists('property_id', $data) ? $data['property_id'] : $expense->property_id,
            'expense_date' => $data['expense_date'] ?? $expense->expense_date,
            'amount' => $data['amount'] ?? $expense->amount,
            'vendor' => $data['vendor'] ?? $expense->vendor,
            'reference_number' => $data['reference_number'] ?? $expense->reference_number,
            'description' => $data['description'] ?? $expense->description,
            'notes' => $data['notes'] ?? $expense->notes,
        ]);

        if ($expense->status === ExpenseStatus::Rejected) {
            $expense->status = ExpenseStatus::Draft;
            $expense->rejection_reason = null;
            $expense->rejected_at = null;
        }

        $expense->save();

        return $expense->fresh(['category', 'property', 'recorder']);
    }

    public function submit(Expense $expense): Expense
    {
        if ($expense->status !== ExpenseStatus::Draft) {
            throw new InvalidArgumentException('Only draft expenses can be submitted.');
        }

        $expense->update([
            'status' => ExpenseStatus::PendingApproval,
            'submitted_at' => now(),
        ]);

        return $expense->fresh();
    }

    public function approve(Expense $expense, User $approver): Expense
    {
        if ($expense->status !== ExpenseStatus::PendingApproval) {
            throw new InvalidArgumentException('Only pending expenses can be approved.');
        }

        if ((int) $expense->recorded_by === (int) $approver->id) {
            throw new InvalidArgumentException('You cannot approve your own expense.');
        }

        $expense->update([
            'status' => ExpenseStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);

        return $expense->fresh(['approver']);
    }

    public function reject(Expense $expense, User $approver, string $reason): Expense
    {
        if ($expense->status !== ExpenseStatus::PendingApproval) {
            throw new InvalidArgumentException('Only pending expenses can be rejected.');
        }

        if ((int) $expense->recorded_by === (int) $approver->id) {
            throw new InvalidArgumentException('You cannot reject your own expense.');
        }

        $expense->update([
            'status' => ExpenseStatus::Rejected,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $expense->fresh();
    }

    public function seedDefaultCategories(Organization $organization): void
    {
        $defaults = [
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet'],
            ['name' => 'Maintenance & repairs', 'description' => 'Building and room upkeep'],
            ['name' => 'Salaries & wages', 'description' => 'Staff payroll'],
            ['name' => 'Supplies', 'description' => 'Cleaning and office supplies'],
            ['name' => 'Taxes & fees', 'description' => 'Government and permit fees'],
            ['name' => 'Other', 'description' => 'Miscellaneous expenses'],
        ];

        DB::transaction(function () use ($organization, $defaults) {
            foreach ($defaults as $row) {
                ExpenseCategory::query()->firstOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name' => $row['name'],
                    ],
                    [
                        'description' => $row['description'],
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
