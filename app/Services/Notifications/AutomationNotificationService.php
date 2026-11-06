<?php

namespace App\Services\Notifications;

use App\Enums\ContractStatus;
use App\Enums\InvoiceStatus;
use App\Enums\OrganizationStatus;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\PaymentReminderNotification;
use App\Support\Notifications\NotificationSettings;
use Illuminate\Support\Collection;

class AutomationNotificationService
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher,
    ) {}

    public function sendPaymentReminders(?int $organizationId = null): int
    {
        $sent = 0;
        $today = now()->startOfDay();

        Organization::query()
            ->when($organizationId, fn ($q) => $q->whereKey($organizationId))
            ->where('status', OrganizationStatus::Active)
            ->each(function (Organization $organization) use ($today, &$sent) {
                $daysList = NotificationSettings::forOrganization($organization)['payment_reminder_days'];

                foreach ($daysList as $daysBefore) {
                    $targetDue = $today->copy()->addDays((int) $daysBefore);

                    Invoice::query()
                        ->where('organization_id', $organization->id)
                        ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid])
                        ->where('balance', '>', 0)
                        ->whereDate('due_date', $targetDue)
                        ->with(['tenant.user', 'organization'])
                        ->each(function (Invoice $invoice) use ($daysBefore, &$sent) {
                            $user = $this->tenantUser($invoice->tenant);
                            if (! $user) {
                                return;
                            }

                            $dedupe = "payment_reminder:invoice:{$invoice->id}:days:{$daysBefore}";
                            if ($this->dispatcher->notifyUser(
                                $user,
                                new PaymentReminderNotification($invoice, (int) $daysBefore),
                                $dedupe,
                                'payment_reminder',
                                $invoice,
                            )) {
                                $sent++;
                            }
                        });
                }
            });

        return $sent;
    }

    public function sendOverdueNotifications(?int $organizationId = null): int
    {
        $sent = 0;

        Invoice::query()
            ->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId))
            ->where('status', InvoiceStatus::Overdue)
            ->where('balance', '>', 0)
            ->with(['tenant.user', 'organization'])
            ->each(function (Invoice $invoice) use (&$sent) {
                $user = $this->tenantUser($invoice->tenant);
                if (! $user) {
                    return;
                }

                $dedupe = 'invoice_overdue:invoice:'.$invoice->id.':'.now()->toDateString();
                if ($this->dispatcher->notifyUser(
                    $user,
                    new InvoiceOverdueNotification($invoice),
                    $dedupe,
                    'invoice_overdue',
                    $invoice,
                )) {
                    $sent++;
                }
            });

        return $sent;
    }

    public function sendContractExpirationReminders(?int $organizationId = null): int
    {
        $sent = 0;
        $today = now()->startOfDay();

        Organization::query()
            ->when($organizationId, fn ($q) => $q->whereKey($organizationId))
            ->where('status', OrganizationStatus::Active)
            ->each(function (Organization $organization) use ($today, &$sent) {
                $daysList = NotificationSettings::forOrganization($organization)['contract_expiry_days'];

                foreach ($daysList as $daysBefore) {
                    $targetEnd = $today->copy()->addDays((int) $daysBefore);

                    Contract::query()
                        ->where('organization_id', $organization->id)
                        ->where('status', ContractStatus::Active)
                        ->whereNotNull('end_date')
                        ->whereDate('end_date', $targetEnd)
                        ->with(['tenant.user', 'organization'])
                        ->each(function (Contract $contract) use ($daysBefore, &$sent) {
                            $tenantUser = $this->tenantUser($contract->tenant);
                            $recipients = collect([$tenantUser])->filter();

                            $staff = $this->dispatcher->organizationStaff($contract->organization, [
                                'manage contracts',
                            ]);

                            $recipients = $recipients->merge($staff)->unique('id');

                            /** @var Collection<int, User> $recipients */
                            $recipients->each(function (User $user) use ($contract, $daysBefore, &$sent) {
                                $dedupe = "contract_expiring:contract:{$contract->id}:days:{$daysBefore}:user:{$user->id}";
                                if ($this->dispatcher->notifyUser(
                                    $user,
                                    new ContractExpiringNotification($contract, (int) $daysBefore),
                                    $dedupe,
                                    'contract_expiring',
                                    $contract,
                                )) {
                                    $sent++;
                                }
                            });
                        });
                }
            });

        return $sent;
    }

    private function tenantUser(?Tenant $tenant): ?User
    {
        if (! $tenant?->user_id) {
            return null;
        }

        $user = $tenant->user;
        if (! $user || ! $user->isActive()) {
            return null;
        }

        return $user;
    }
}
