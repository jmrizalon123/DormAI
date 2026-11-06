<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
        'country',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'approved_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function owner(): HasMany
    {
        return $this->hasMany(User::class)->where('account_type', 'organization_owner');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isActive(): bool
    {
        return $this->status === OrganizationStatus::Active;
    }

    public function isPending(): bool
    {
        return $this->status === OrganizationStatus::Pending;
    }
}
