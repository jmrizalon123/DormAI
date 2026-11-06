<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithOrganization
{
    protected function canManageProperties(User $user): bool
    {
        return $user->can('manage properties');
    }

    protected function canManageTenants(User $user): bool
    {
        return $user->can('manage tenants');
    }

    protected function belongsToActiveOrganization(Model $model, OrganizationContext $organizationContext): bool
    {
        if (! isset($model->organization_id)) {
            return false;
        }

        $organizationId = $organizationContext->id();

        return $organizationId !== null
            && (int) $model->organization_id === $organizationId;
    }
}
