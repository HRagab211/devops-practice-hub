<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Queue::looping(function (): void {
    Queue::size();
    touch('/tmp/hub-worker-heartbeat');
});

exit($app->handleCommand(new ArgvInput([
    'artisan', 'queue:work', '--queue=default', '--sleep=3', '--tries=3', '--timeout=60',
])));
