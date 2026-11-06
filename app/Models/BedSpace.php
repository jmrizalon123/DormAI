<?php

namespace App\Models;

use App\Enums\BedSpaceStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\BedSpaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BedSpace extends Model
{
    /** @use HasFactory<BedSpaceFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'room_id',
        'label',
        'monthly_rate',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => BedSpaceStatus::class,
            'monthly_rate' => 'decimal:2',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TenantAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(TenantAssignment::class)->where('status', 'active');
    }
}
