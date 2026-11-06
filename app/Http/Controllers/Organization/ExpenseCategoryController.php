<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\Finance\ExpenseService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(OrganizationContext $organizationContext, ExpenseService $expenseService): View
    {
        $this->authorize('viewAny', ExpenseCategory::class);

        $organization = $organizationContext->ensureOrganization();

        if (ExpenseCategory::query()->where('organization_id', $organization->id)->count() === 0) {
            $expenseService->seedDefaultCategories($organization);
        }

        $categories = ExpenseCategory::query()
            ->where('organization_id', $organization->id)
            ->withCount('expenses')
            ->orderBy('name')
            ->paginate(15);

        return view('organization.finance.categories.index', compact('categories'));
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', ExpenseCategory::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        ExpenseCategory::query()->create([
            'organization_id' => $organizationContext->ensureId(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->authorize('update', $expenseCategory);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $expenseCategory->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', $expenseCategory->is_active),
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->authorize('delete', $expenseCategory);

        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'Cannot delete a category that has expenses.');
        }

        $expenseCategory->delete();

        return back()->with('success', 'Category removed.');
    }
}
