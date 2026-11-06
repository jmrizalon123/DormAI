<?php

namespace App\Models;

use App\Enums\UtilityType;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityMeter extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'property_id',
        'room_id',
        'utility_type',
        'label',
        'meter_number',
        'is_shared',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'utility_type' => UtilityType::class,
            'is_shared' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(UtilityReading::class);
    }

    public function latestReading(): ?UtilityReading
    {
        return $this->readings()->orderByDesc('reading_date')->orderByDesc('id')->first();
    }
}
