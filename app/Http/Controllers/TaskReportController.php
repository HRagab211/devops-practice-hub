<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTaskReport;
use App\Models\TaskReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TaskReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports', ['reports' => $request->user()->reports()->latest('id')->paginate(10)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $report = $request->user()->reports()->create(['status' => 'pending', 'disk' => config('hub.report_disk')]);
        try {
            GenerateTaskReport::dispatch($report->id)->afterCommit();
        } catch (Throwable $exception) {
            $report->update(['status' => 'failed']);
            Log::error('Report dispatch failed', ['report_id' => $report->id, 'exception' => $exception]);

            return to_route('reports.index')->with('error', 'Your report could not be queued. Please try again.');
        }

        return to_route('reports.index')->with('status', 'Report requested. Refresh this page to see its progress.');
    }

    public function download(TaskReport $report): StreamedResponse
    {
        Gate::authorize('view', $report);
        abort_unless($report->status === 'completed' && $report->path, 404);
        abort_unless(Storage::disk($report->disk)->exists($report->path), 404);

        return Storage::disk($report->disk)->download($report->path, 'task-report-'.$report->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
