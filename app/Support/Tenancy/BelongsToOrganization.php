<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait BelongsToOrganization
{
    public function scopeForOrganization(Builder $query, ?int $organizationId): Builder
    {
        if ($organizationId === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where($this->getTable().'.organization_id', $organizationId);
    }

    public function belongsToOrganization(?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        return (int) $this->getAttribute('organization_id') === $organizationId;
    }
}
