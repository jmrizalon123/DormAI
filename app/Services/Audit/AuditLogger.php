<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function log(
        string $event,
        ?Model $auditable = null,
        array $properties = [],
        ?int $organizationId = null,
    ): AuditLog {
        $request = request();

        return AuditLog::query()->create([
            'organization_id' => $organizationId ?? $this->organizationContext->id(),
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
