<?php

namespace App\Models;

use App\Enums\UtilityAllocationStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityAllocation extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'utility_reading_id',
        'room_id',
        'tenant_id',
        'consumption_units',
        'rate_per_unit',
        'amount',
        'status',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => UtilityAllocationStatus::class,
            'consumption_units' => 'decimal:3',
            'rate_per_unit' => 'decimal:4',
            'amount' => 'decimal:2',
        ];
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(UtilityReading::class, 'utility_reading_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
