<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskReportResource;
use App\Http\Resources\TaskResource;
use App\Services\DashboardCounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardCounts $counts): JsonResponse|View
    {
        $user = $request->user();

        if (! $request->wantsJson()) {
            return view('dashboard', ['user' => $user]);
        }

        $report = $user->reports()->latest('id')->first();

        return response()->json(['data' => [
            'counts' => $counts->forUser($user),
            'recent_tasks' => TaskResource::collection($user->tasks()->latest('updated_at')->orderByDesc('id')->limit(5)->get()),
            'latest_report' => $report ? TaskReportResource::make($report) : null,
        ]]);
    }
}
