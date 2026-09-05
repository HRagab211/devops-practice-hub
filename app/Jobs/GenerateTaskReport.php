<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateTaskReport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public bool $failOnTimeout = true;

    /** @var list<int> */
    public array $backoff = [10, 30, 60];

    public function __construct(public int $reportId) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('report:'.$this->reportId))->releaseAfter(10)->expireAfter(75)];
    }

    public function handle(): void
    {
        $report = TaskReport::findOrFail($this->reportId);
        if ($report->status === 'completed') {
            return;
        }
        $report->update(['status' => 'processing', 'started_at' => now(), 'completed_at' => null]);
        Log::info('Task report processing', ['report_id' => $report->id, 'user_id' => $report->user_id, 'attempt' => $this->attempts()]);
        $stream = fopen('php://temp/maxmemory:2097152', 'w+');
        try {
            fputcsv($stream, ['Title', 'Description', 'Status', 'Due date', 'Created at', 'Updated at'], ',', '"', '');
            foreach (Task::where('user_id', $report->user_id)->lazyById(500) as $task) {
                $cells = [$task->title, $task->description ?? '', $task->status, $task->due_date?->format('Y-m-d') ?? '', $task->created_at->toIso8601String(), $task->updated_at->toIso8601String()];
                fputcsv($stream, array_map($this->safeCell(...), $cells), ',', '"', '');
            }
            rewind($stream);
            $path = 'users/'.$report->user_id.'/report-'.$report->id.'.csv';
            if (! Storage::disk($report->disk)->put($path, $stream, ['visibility' => 'private'])) {
                throw new \RuntimeException('Report storage failed.');
            }
            $report->update(['status' => 'completed', 'path' => $path, 'completed_at' => now()]);
            Log::info('Task report completed', ['report_id' => $report->id]);
        } catch (Throwable $exception) {
            Log::warning('Task report attempt failed', ['report_id' => $report->id, 'exception' => $exception]);
            throw $exception;
        } finally {
            fclose($stream);
        }
    }

    private function safeCell(string $value): string
    {
        return preg_match('/^[\s\x00-\x1f]*[=+@\-]|^[\t\r\n]/u', $value) ? "'".$value : $value;
    }

    public function failed(?Throwable $exception): void
    {
        TaskReport::whereKey($this->reportId)->where('status', '!=', 'completed')->update(['status' => 'failed']);
        Log::error('Task report failed', ['report_id' => $this->reportId, 'exception' => $exception]);
    }
}
