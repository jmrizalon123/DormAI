<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAssignmentHistory extends Model
{
    use BelongsToOrganization;

    public $timestamps = false;

    protected $table = 'tenant_assignment_history';

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'tenant_assignment_id',
        'event',
        'property_id',
        'room_id',
        'bed_space_id',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TenantAssignment::class, 'tenant_assignment_id');
    }
}
