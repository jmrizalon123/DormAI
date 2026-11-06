<?php

namespace App\Http\Controllers\Organization;

use App\Enums\UserAccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationUserRequest;
use App\Http\Requests\Organization\UpdateOrganizationUserRequest;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->where('account_type', '!=', UserAccountType::SuperAdmin->value)
            ->with('roles')
            ->latest()
            ->paginate(15);

        return view('organization.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('organization.users.create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(
        StoreOrganizationUserRequest $request,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $organizationId = $organizationContext->ensureId();

        DB::transaction(function () use ($request, $organizationId, $auditLogger) {
            $user = User::query()->create([
                'organization_id' => $organizationId,
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'password' => $request->validated('password'),
                'account_type' => UserAccountType::Staff,
                'status' => $request->validated('status'),
            ]);

            setPermissionsTeamId($organizationId);
            $user->syncRoles([$request->validated('role')]);

            $auditLogger->log('organization.user.created', $user, [
                'role' => $request->validated('role'),
            ]);
        });

        return redirect()
            ->route('organization.users.index')
            ->with('success', 'Team member created successfully.');
    }

    public function edit(User $user, OrganizationContext $organizationContext): View
    {
        $this->authorize('update', $user);
        $this->assertSameOrganization($user, $organizationContext);

        return view('organization.users.edit', [
            'user' => $user->load('roles'),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(
        UpdateOrganizationUserRequest $request,
        User $user,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $this->assertSameOrganization($user, $organizationContext);

        DB::transaction(function () use ($request, $user, $organizationContext, $auditLogger) {
            $payload = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'status' => $request->validated('status'),
            ];

            if ($request->filled('password')) {
                $payload['password'] = $request->validated('password');
            }

            $user->update($payload);

            setPermissionsTeamId($organizationContext->ensureId());
            $user->syncRoles([$request->validated('role')]);

            $auditLogger->log('organization.user.updated', $user, [
                'role' => $request->validated('role'),
            ]);
        });

        return redirect()
            ->route('organization.users.index')
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(
        User $user,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $this->authorize('delete', $user);
        $this->assertSameOrganization($user, $organizationContext);

        DB::transaction(function () use ($user, $auditLogger) {
            $auditLogger->log('organization.user.deleted', $user);
            $user->delete();
        });

        return redirect()
            ->route('organization.users.index')
            ->with('success', 'Team member removed.');
    }

    private function assertSameOrganization(User $user, OrganizationContext $organizationContext): void
    {
        if ((int) $user->organization_id !== $organizationContext->ensureId()) {
            abort(404);
        }
    }

    /**
     * @return list<string>
     */
    private function assignableRoles(): array
    {
        return [
            'property-manager',
            'receptionist',
            'cashier',
            'maintenance-staff',
            'accountant',
        ];
    }
}
