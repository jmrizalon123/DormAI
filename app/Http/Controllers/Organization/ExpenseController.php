<?php

namespace App\Http\Controllers\Organization;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Property;
use App\Services\Finance\ExpenseService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Expense::class);

        $organizationId = $organizationContext->ensureId();

        $expenses = Expense::query()
            ->where('organization_id', $organizationId)
            ->with(['category', 'property', 'recorder'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('expense_category_id', $request->integer('category')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhere('vendor', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('expense_date')
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pendingTotal = (float) Expense::query()
            ->where('organization_id', $organizationId)
            ->where('status', ExpenseStatus::PendingApproval)
            ->sum('amount');

        return view('organization.finance.expenses.index', compact('expenses', 'categories', 'pendingTotal'));
    }

    public function create(OrganizationContext $organizationContext): View
    {
        $this->authorize('create', Expense::class);

        return view('organization.finance.expenses.create', $this->formData($organizationContext));
    }

    public function store(Request $request, OrganizationContext $organizationContext, ExpenseService $expenseService): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $validated = $this->validatedExpense($request, $organizationContext);

        $expense = $expenseService->create(
            $organizationContext->ensureOrganization(),
            $request->user(),
            $validated,
        );

        if ($request->boolean('submit_now')) {
            $expenseService->submit($expense);
        }

        return redirect()
            ->route('organization.expenses.show', $expense)
            ->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense): View
    {
        $this->authorize('view', $expense);

        $expense->load(['category', 'property', 'recorder', 'approver', 'attachments.uploader']);

        return view('organization.finance.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense, OrganizationContext $organizationContext): View
    {
        $this->authorize('update', $expense);

        return view('organization.finance.expenses.edit', array_merge(
            ['expense' => $expense],
            $this->formData($organizationContext),
        ));
    }

    public function update(Request $request, Expense $expense, ExpenseService $expenseService): RedirectResponse
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'vendor' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->assertPropertyInOrg($validated['property_id'] ?? null, $expense->organization_id);

        $expenseService->update($expense, $validated);

        return redirect()
            ->route('organization.expenses.show', $expense)
            ->with('success', 'Expense updated.');
    }

    public function submit(Expense $expense, ExpenseService $expenseService): RedirectResponse
    {
        $this->authorize('submit', $expense);

        try {
            $expenseService->submit($expense);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Expense submitted for approval.');
    }

    public function approve(Request $request, Expense $expense, ExpenseService $expenseService): RedirectResponse
    {
        $this->authorize('approve', $expense);

        try {
            $expenseService->approve($expense, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense, ExpenseService $expenseService): RedirectResponse
    {
        $this->authorize('approve', $expense);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $expenseService->reject($expense, $request->user(), $validated['rejection_reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Expense rejected.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(OrganizationContext $organizationContext): array
    {
        $organizationId = $organizationContext->ensureId();

        return [
            'categories' => ExpenseCategory::query()
                ->where('organization_id', $organizationId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'properties' => Property::query()
                ->where('organization_id', $organizationId)
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExpense(Request $request, OrganizationContext $organizationContext): array
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'vendor' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'submit_now' => ['sometimes', 'boolean'],
        ]);

        ExpenseCategory::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereKey($validated['expense_category_id'])
            ->firstOrFail();

        $this->assertPropertyInOrg($validated['property_id'] ?? null, $organizationContext->ensureId());

        return $validated;
    }

    private function assertPropertyInOrg(?int $propertyId, int $organizationId): void
    {
        if ($propertyId === null) {
            return;
        }

        Property::query()
            ->where('organization_id', $organizationId)
            ->whereKey($propertyId)
            ->firstOrFail();
    }
}
