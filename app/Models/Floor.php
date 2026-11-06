<?php

namespace App\Models;

use App\Enums\FloorStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\FloorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    /** @use HasFactory<FloorFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'building_id',
        'name',
        'level_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FloorStatus::class,
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
