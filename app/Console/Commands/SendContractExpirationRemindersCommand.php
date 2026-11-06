<?php

namespace App\Console\Commands;

use App\Services\Notifications\AutomationNotificationService;
use Illuminate\Console\Command;

class SendContractExpirationRemindersCommand extends Command
{
    protected $signature = 'dormai:send-contract-expiration-reminders {--organization= : Organization ID}';

    protected $description = 'Send contract expiration reminders to tenants and staff';

    public function handle(AutomationNotificationService $automation): int
    {
        $orgId = $this->option('organization') ? (int) $this->option('organization') : null;
        $count = $automation->sendContractExpirationReminders($orgId);
        $this->info("Sent {$count} contract expiration reminder(s).");

        return self::SUCCESS;
    }
}
