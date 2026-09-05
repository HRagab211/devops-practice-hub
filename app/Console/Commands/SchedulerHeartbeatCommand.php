<?php

namespace App\Console\Commands;

use App\Models\SchedulerHeartbeat;
use Illuminate\Console\Command;

class SchedulerHeartbeatCommand extends Command
{
    protected $signature = 'lab:heartbeat';

    protected $description = 'Persist the latest scheduler heartbeat';

    public function handle(): int
    {
        SchedulerHeartbeat::upsert([['id' => 'scheduler', 'last_ran_at' => now()]], ['id'], ['last_ran_at']);
        $this->info('Scheduler heartbeat updated.');

        return self::SUCCESS;
    }
}
