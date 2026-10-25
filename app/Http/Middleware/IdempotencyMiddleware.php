<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        # 1- get the sent idempotency-key
        $key = $request->header('Idempotency-Key');
        # 2- if key not sent return err
        if (!$key || strlen($key) > 255) {
            return response()->json([
                'valid idempotency key is required'
            ], 400);
        }
        $cache_key = 'idempotency' . hash("sha256", json_encode([
            $request->user()->id,
            $request->method(),
            $request->path(),
            $key
        ]));

        $fingreprint = hash("sha256", $request->getContent());

        $lock = Cache::lock($cache_key . ":lock", 60);

        if (!$lock->get()) {
            return response()->json([
                'another process is still running'
            ], 409);
        }

        // dd($cache_key);
        return $next($request);
    }
}
