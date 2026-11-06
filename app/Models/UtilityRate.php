<?php

namespace App\Models;

use App\Enums\UtilityType;
use App\Support\Tenancy\BelongsToOrganization;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityRate extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'property_id',
        'utility_type',
        'rate_per_unit',
        'unit_label',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'utility_type' => UtilityType::class,
            'rate_per_unit' => 'decimal:4',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public static function resolveFor(Property $property, UtilityType $type, CarbonInterface $onDate): ?self
    {
        return self::query()
            ->where('organization_id', $property->organization_id)
            ->where('property_id', $property->id)
            ->where('utility_type', $type)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $onDate)
            ->where(function ($q) use ($onDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $onDate);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
