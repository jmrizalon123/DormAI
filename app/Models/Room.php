<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'property_id',
        'building_id',
        'floor_id',
        'room_type_id',
        'room_number',
        'capacity',
        'monthly_rate',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'monthly_rate' => 'decimal:2',
            'capacity' => 'integer',
        ];
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

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bedSpaces(): HasMany
    {
        return $this->hasMany(BedSpace::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(RoomAmenity::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }
}
