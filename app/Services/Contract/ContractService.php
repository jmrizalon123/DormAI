<?php

namespace App\Services\Contract;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Organization;
use App\Models\Reservation;
use App\Models\Tenant;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function ensureDefaultTemplate(Organization $organization): ContractTemplate
    {
        $existing = ContractTemplate::query()
            ->where('organization_id', $organization->id)
            ->where('is_default', true)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return ContractTemplate::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Standard boarding house agreement',
            'is_default' => true,
            'body' => implode("\n\n", [
                'This rental agreement is entered into between {{organization_name}} and {{tenant_name}}.',
                'Property: {{property_name}} · Room {{room_number}} · Bed {{bed_label}}.',
                'Monthly rate: PHP {{monthly_rate}}. Security deposit: PHP {{security_deposit}}. Advance payment: PHP {{advance_payment}}.',
                'Term: {{start_date}} to {{end_date}}.',
                'The tenant agrees to comply with house rules, payment schedules, and move-out procedures.',
            ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function createFromReservation(Reservation $reservation, Tenant $tenant, array $overrides = []): Contract
    {
        $organization = Organization::query()->findOrFail($reservation->organization_id);
        $template = $this->ensureDefaultTemplate($organization);

        $monthlyRate = $overrides['monthly_rate']
            ?? $reservation->bedSpace->monthly_rate
            ?? $reservation->room->monthly_rate;
        $securityDeposit = $overrides['security_deposit'] ?? ($reservation->deposit_amount ?? 0);
        $advancePayment = $overrides['advance_payment'] ?? 0;
        $startDate = $overrides['start_date'] ?? $reservation->requested_check_in->toDateString();
        $endDate = $overrides['end_date'] ?? $reservation->requested_check_out?->toDateString();

        $terms = $this->renderTemplate($template->body, [
            'organization_name' => $organization->name,
            'tenant_name' => $tenant->fullName(),
            'property_name' => $reservation->property->name,
            'room_number' => $reservation->room->room_number,
            'bed_label' => $reservation->bedSpace->label,
            'monthly_rate' => number_format((float) $monthlyRate, 2),
            'security_deposit' => number_format((float) $securityDeposit, 2),
            'advance_payment' => number_format((float) $advancePayment, 2),
            'start_date' => $startDate,
            'end_date' => $endDate ?? 'Open-ended',
        ]);

        return DB::transaction(function () use ($reservation, $tenant, $template, $monthlyRate, $securityDeposit, $advancePayment, $startDate, $endDate, $terms) {
            $contract = Contract::query()->create([
                'organization_id' => $reservation->organization_id,
                'tenant_id' => $tenant->id,
                'reservation_id' => $reservation->id,
                'property_id' => $reservation->property_id,
                'room_id' => $reservation->room_id,
                'bed_space_id' => $reservation->bed_space_id,
                'contract_template_id' => $template->id,
                'contract_number' => $this->generateContractNumber($reservation->organization_id),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'monthly_rate' => $monthlyRate,
                'security_deposit' => $securityDeposit,
                'advance_payment' => $advancePayment,
                'terms' => $terms,
                'status' => ContractStatus::Active,
                'signed_at' => now(),
            ]);

            $this->storePrintableCopy($contract);
            $this->auditLogger->log('contract.created', $contract);

            return $contract;
        });
    }

    public function renew(Contract $contract, string $newEndDate, ?float $monthlyRate = null): Contract
    {
        return DB::transaction(function () use ($contract, $newEndDate, $monthlyRate) {
            $contract->update(['status' => ContractStatus::Renewed]);

            $renewed = Contract::query()->create([
                'organization_id' => $contract->organization_id,
                'tenant_id' => $contract->tenant_id,
                'property_id' => $contract->property_id,
                'room_id' => $contract->room_id,
                'bed_space_id' => $contract->bed_space_id,
                'contract_template_id' => $contract->contract_template_id,
                'previous_contract_id' => $contract->id,
                'contract_number' => $this->generateContractNumber($contract->organization_id),
                'start_date' => $contract->end_date?->toDateString() ?? now()->toDateString(),
                'end_date' => $newEndDate,
                'monthly_rate' => $monthlyRate ?? $contract->monthly_rate,
                'security_deposit' => $contract->security_deposit,
                'advance_payment' => 0,
                'terms' => $contract->terms,
                'status' => ContractStatus::Active,
                'signed_at' => now(),
            ]);

            $this->storePrintableCopy($renewed);
            $this->auditLogger->log('contract.renewed', $renewed, ['previous_id' => $contract->id]);

            return $renewed;
        });
    }

    /**
     * @param  array<string, string>  $replacements
     */
    public function renderTemplate(string $body, array $replacements): string
    {
        $rendered = $body;
        foreach ($replacements as $key => $value) {
            $rendered = str_replace('{{'.$key.'}}', $value, $rendered);
        }

        return $rendered;
    }

    private function generateContractNumber(int $organizationId): string
    {
        do {
            $number = 'CNT-'.now()->format('ym').'-'.strtoupper(Str::random(6));
        } while (Contract::query()->where('organization_id', $organizationId)->where('contract_number', $number)->exists());

        return $number;
    }

    private function storePrintableCopy(Contract $contract): void
    {
        $contract->load(['tenant', 'property', 'room', 'bedSpace']);
        $html = view('organization.contracts.print', ['contract' => $contract])->render();
        $path = 'contracts/'.$contract->organization_id.'/'.$contract->contract_number.'.html';
        Storage::disk('local')->put($path, $html);
        $contract->update(['pdf_path' => $path]);
    }
}
