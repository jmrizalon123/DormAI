<?php

namespace App\Models;

use App\Enums\TenantStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address_line1',
        'city',
        'province',
        'school_or_work',
        'valid_id_type',
        'valid_id_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'date_of_birth' => 'date',
        ];
    }

    public function fullName(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->join(' '));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(TenantEmergencyContact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenantDocument::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TenantAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(TenantAssignment::class)->ofMany(['id' => 'max'], function ($query) {
            $query->where('status', 'active');
        });
    }

    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(TenantAssignmentHistory::class)->latest('created_at');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(TenantPaymentSubmission::class);
    }

    public function hasPortalAccess(): bool
    {
        return $this->user_id !== null;
    }
}
