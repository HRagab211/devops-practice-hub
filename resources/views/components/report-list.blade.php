@props(['reports'])<div class="divide-y divide-slate-100">
@forelse($reports as $report)<article class="flex flex-wrap items-center justify-between gap-4 py-5">
<div>
<p class="font-medium">Task report #{{ $report->id }}</p>
<p class="mt-1 text-xs text-slate-500">{{ $report->created_at->format('M j, Y · H:i') }}</p>
@if($report->status==='failed')<p class="mt-2 text-sm text-red-700">This report failed. Please request a new report.</p>
@endif
</div>
<div class="flex items-center gap-4">
<x-status-badge :status="$report->status"/>
@if($report->status==='completed')<a class="text-sm font-medium text-brand" href="{{ route('reports.download',$report) }}">Download CSV<span class="sr-only"> report {{ $report->id }}</span>
</a>
@endif
</div>
</article>
@empty
<p class="py-8 text-sm text-slate-500">No reports yet. Generate your first task report to get started.</p>
@endforelse
</div>
