<?php

namespace App\Console\Commands;

use App\Services\Notifications\AutomationNotificationService;
use Illuminate\Console\Command;

class SendOverdueInvoiceNotificationsCommand extends Command
{
    protected $signature = 'dormai:send-overdue-notifications {--organization= : Organization ID}';

    protected $description = 'Notify tenants about overdue invoices';

    public function handle(AutomationNotificationService $automation): int
    {
        $orgId = $this->option('organization') ? (int) $this->option('organization') : null;
        $count = $automation->sendOverdueNotifications($orgId);
        $this->info("Sent {$count} overdue notification(s).");

        return self::SUCCESS;
    }
}
