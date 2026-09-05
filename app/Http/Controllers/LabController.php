<?php

namespace App\Http\Controllers;

use App\Models\SchedulerHeartbeat;
use App\Services\ReadinessChecks;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabController extends Controller
{
    public function __invoke(Request $request, ReadinessChecks $checks): View
    {
        abort_unless(config('hub.lab_enabled'), 404);

        return view('lab', ['checks' => $checks->run(), 'heartbeat' => SchedulerHeartbeat::find('scheduler'), 'reports' => $request->user()->reports()->latest('id')->limit(10)->get()]);
    }
}
