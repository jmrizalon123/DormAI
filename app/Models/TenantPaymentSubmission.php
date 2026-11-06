<?php

namespace App\Models;

use App\Enums\PaymentSubmissionStatus;
use App\Support\Tenancy\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPaymentSubmission extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'tenant_id',
        'invoice_id',
        'submitted_by',
        'reviewed_by',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'proof_path',
        'status',
        'rejection_reason',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentSubmissionStatus::class,
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === PaymentSubmissionStatus::Pending;
    }
}
