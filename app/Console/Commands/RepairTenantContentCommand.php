<?php

namespace App\Console\Commands;

use App\Services\TenantContentRepairService;
use Illuminate\Console\Command;

class RepairTenantContentCommand extends Command
{
    protected $signature = 'tenant:repair';

    protected $description = 'Backfill tenant_id on legacy content and link users to the default workspace';

    public function handle(TenantContentRepairService $repair): int
    {
        $result = $repair->repair();

        $this->info('Default workspace tenant #'.$result['tenant_id']);

        foreach ($result['backfilled'] as $table => $count) {
            if ($count > 0) {
                $this->line("  {$table}: {$count} row(s) updated");
            }
        }

        $this->info('Users newly linked to workspace: '.$result['users_linked']);

        return self::SUCCESS;
    }
}
