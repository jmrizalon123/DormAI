<?php

namespace App\Services\Portal;

use App\Enums\PaymentMethod;
use App\Enums\PaymentSubmissionStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\TenantPaymentSubmission;
use App\Models\User;
use App\Services\Billing\PaymentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TenantPaymentSubmissionService
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(
        Organization $organization,
        Tenant $tenant,
        User $submitter,
        array $data,
        ?UploadedFile $proof = null,
    ): TenantPaymentSubmission {
        if ((int) $tenant->organization_id !== (int) $organization->id) {
            throw ValidationException::withMessages(['tenant' => ['Invalid tenant.']]);
        }

        $invoice = null;
        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::query()
                ->where('organization_id', $organization->id)
                ->where('tenant_id', $tenant->id)
                ->whereKey($data['invoice_id'])
                ->firstOrFail();
        }

        $proofPath = null;
        if ($proof !== null) {
            $proofPath = $proof->store(
                'payment-proofs/'.$organization->id.'/'.$tenant->id,
                'local',
            );
        }

        return TenantPaymentSubmission::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice?->id,
            'submitted_by' => $submitter->id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'],
            'notes' => $data['notes'] ?? null,
            'proof_path' => $proofPath,
            'status' => PaymentSubmissionStatus::Pending,
        ]);
    }

    public function approve(TenantPaymentSubmission $submission, User $reviewer): TenantPaymentSubmission
    {
        if (! $submission->isPending()) {
            throw new InvalidArgumentException('Only pending submissions can be approved.');
        }

        return DB::transaction(function () use ($submission, $reviewer) {
            $submission->loadMissing(['tenant', 'organization']);
            $organization = $submission->organization;
            $tenant = $submission->tenant;

            $allocations = [];
            if ($submission->invoice_id !== null) {
                $allocations[] = [
                    'invoice_id' => $submission->invoice_id,
                    'amount' => (float) $submission->amount,
                ];
            }

            $this->paymentService->record(
                $organization,
                $tenant,
                (float) $submission->amount,
                PaymentMethod::from($submission->payment_method),
                $allocations,
                $reviewer,
                $submission->reference_number,
                trim('Tenant portal submission #'.$submission->id.($submission->notes ? ' — '.$submission->notes : '')),
            );

            $submission->update([
                'status' => PaymentSubmissionStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return $submission->fresh(['tenant', 'invoice', 'submitter', 'reviewer']);
        });
    }

    public function reject(TenantPaymentSubmission $submission, User $reviewer, string $reason): TenantPaymentSubmission
    {
        if (! $submission->isPending()) {
            throw new InvalidArgumentException('Only pending submissions can be rejected.');
        }

        $submission->update([
            'status' => PaymentSubmissionStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $submission->fresh();
    }
}
