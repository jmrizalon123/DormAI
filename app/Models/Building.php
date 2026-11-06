<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\BuildingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    /** @use HasFactory<BuildingFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'property_id',
        'name',
        'description',
        'sort_order',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class)->orderBy('level_number');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
