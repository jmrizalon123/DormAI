<?php

namespace App\Console\Commands;

use App\Services\Notifications\AutomationNotificationService;
use Illuminate\Console\Command;

class SendPaymentRemindersCommand extends Command
{
    protected $signature = 'dormai:send-payment-reminders {--organization= : Organization ID}';

    protected $description = 'Send payment reminder notifications for invoices approaching due date';

    public function handle(AutomationNotificationService $automation): int
    {
        $orgId = $this->option('organization') ? (int) $this->option('organization') : null;
        $count = $automation->sendPaymentReminders($orgId);
        $this->info("Sent {$count} payment reminder notification(s).");

        return self::SUCCESS;
    }
}
