<?php

namespace App\Http\Controllers;

use App\Services\ReadinessChecks;
use Illuminate\Http\JsonResponse;

class ReadinessController extends Controller
{
    public function __invoke(ReadinessChecks $checks): JsonResponse
    {
        $results = $checks->run();
        $ready = $results['database'] && $results['cache'];

        return response()->json(['ready' => $ready], $ready ? 200 : 503)->header('Cache-Control', 'no-store');
    }
}
