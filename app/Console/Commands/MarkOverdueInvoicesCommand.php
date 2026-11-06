<?php

namespace App\Console\Commands;

use App\Services\Billing\InvoiceService;
use Illuminate\Console\Command;

class MarkOverdueInvoicesCommand extends Command
{
    protected $signature = 'dormai:mark-overdue-invoices';

    protected $description = 'Mark issued invoices past due date and grace period as overdue';

    public function handle(InvoiceService $invoiceService): int
    {
        $count = $invoiceService->markOverdueInvoices();
        $this->info("Marked {$count} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}
