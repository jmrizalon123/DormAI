<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'status',
        'description',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => PropertyStatus::class,
            'settings' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }
}
