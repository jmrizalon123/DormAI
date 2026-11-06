<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'reservation_id',
        'property_id',
        'room_id',
        'bed_space_id',
        'contract_template_id',
        'previous_contract_id',
        'contract_number',
        'start_date',
        'end_date',
        'monthly_rate',
        'security_deposit',
        'advance_payment',
        'terms',
        'status',
        'pdf_path',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_rate' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'advance_payment' => 'decimal:2',
            'signed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bedSpace(): BelongsTo
    {
        return $this->belongsTo(BedSpace::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'contract_template_id');
    }

    public function previousContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'previous_contract_id');
    }
}
