<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'payment_number',
        'paid_at',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function allocatedAmount(): float
    {
        return (float) $this->allocations()->sum('amount');
    }
}
