<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityReading extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'utility_meter_id',
        'recorded_by',
        'reading_date',
        'previous_reading',
        'current_reading',
        'consumption',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reading_date' => 'date',
            'previous_reading' => 'decimal:3',
            'current_reading' => 'decimal:3',
            'consumption' => 'decimal:3',
        ];
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(UtilityMeter::class, 'utility_meter_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(UtilityAllocation::class);
    }
}
