<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;

class BootstrapApplication extends Command
{
    protected $signature = 'opswiki:bootstrap';

    protected $description = 'Run production bootstrap seeder once (skipped if already bootstrapped)';

    public function handle(): int
    {
        if (Plan::query()->exists()) {
            $this->info('Bootstrap skipped — application already initialized.');

            return self::SUCCESS;
        }

        $this->info('First deploy detected — running production bootstrap seeder...');

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\ProductionBootstrapSeeder',
            '--force' => true,
        ]);

        $this->info('Bootstrap complete.');

        return self::SUCCESS;
    }
}
