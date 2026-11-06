<?php

namespace App\Models;

use App\Enums\TenantAssignmentStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAssignment extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'property_id',
        'building_id',
        'floor_id',
        'room_id',
        'bed_space_id',
        'move_in_date',
        'move_out_date',
        'status',
        'ended_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => TenantAssignmentStatus::class,
            'move_in_date' => 'date',
            'move_out_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bedSpace(): BelongsTo
    {
        return $this->belongsTo(BedSpace::class);
    }
}
