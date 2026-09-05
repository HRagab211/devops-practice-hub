<?php

use App\Models\SchedulerHeartbeat;
use Illuminate\Contracts\Console\Kernel;

try {
    if (($argv[1] ?? '') === 'worker') {
        $path = '/tmp/hub-worker-heartbeat';
        exit(is_file($path) && time() - filemtime($path) < 90 ? 0 : 1);
    }

    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    $heartbeat = SchedulerHeartbeat::find('scheduler');
    exit($heartbeat && $heartbeat->last_ran_at->greaterThan(now()->subSeconds(150)) ? 0 : 1);
} catch (Throwable) {
    exit(1);
}
