<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'property_id',
        'room_id',
        'bed_space_id',
        'tenant_id',
        'reference_code',
        'applicant_first_name',
        'applicant_last_name',
        'applicant_email',
        'applicant_phone',
        'requested_check_in',
        'requested_check_out',
        'status',
        'deposit_amount',
        'deposit_paid_at',
        'expires_at',
        'applicant_notes',
        'staff_notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'checked_in_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'requested_check_in' => 'date',
            'requested_check_out' => 'date',
            'deposit_amount' => 'decimal:2',
            'deposit_paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function applicantFullName(): string
    {
        return trim($this->applicant_first_name.' '.$this->applicant_last_name);
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }
}
