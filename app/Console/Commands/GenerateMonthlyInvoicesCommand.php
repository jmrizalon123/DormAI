<?php

namespace App\Console\Commands;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Services\Billing\InvoiceService;
use App\Support\Billing\BillingSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoicesCommand extends Command
{
    protected $signature = 'dormai:generate-monthly-invoices {--organization= : Organization ID} {--month= : YYYY-MM}';

    protected $description = 'Generate and issue monthly rent invoices for active contracts';

    public function handle(InvoiceService $invoiceService): int
    {
        $month = $this->option('month')
            ? Carbon::parse($this->option('month').'-01')
            : now();

        $query = Organization::query()->where('status', OrganizationStatus::Active);
        if ($this->option('organization')) {
            $query->whereKey($this->option('organization'));
        }

        $total = 0;

        $query->each(function (Organization $organization) use ($invoiceService, $month, &$total) {
            if (! BillingSettings::forOrganization($organization)['auto_generate_monthly']) {
                return;
            }

            $created = $invoiceService->generateMonthlyInvoices($organization, $month);
            $total += $created->count();
            $this->line("{$organization->name}: {$created->count()} invoice(s)");
        });

        $this->info("Generated {$total} invoice(s).");

        return self::SUCCESS;
    }
}
