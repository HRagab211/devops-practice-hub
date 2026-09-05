<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ReadinessChecks
{
    /** @return array{database:bool,cache:bool} */
    public function run(): array
    {
        $database = false;
        $cache = false;
        try {
            DB::connection()->select('SELECT 1');
            $database = true;
        } catch (Throwable) {
        }
        $key = 'readiness:'.Str::uuid();
        try {
            $cache = Cache::put($key, 'ready', 10) && Cache::get($key) === 'ready';
            Cache::forget($key);
        } catch (Throwable) {
            $cache = false;
        }

        return compact('database', 'cache');
    }
}
