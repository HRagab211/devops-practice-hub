@props(['tasks'])<div class="divide-y divide-slate-100">
@forelse($tasks as $task)<article class="flex flex-wrap items-center justify-between gap-4 py-5">
<div class="min-w-0">
<a class="break-words font-medium hover:text-brand" href="{{ route('tasks.show',$task) }}">{{ $task->title }}</a>
<p class="mt-1 text-xs text-slate-500">{{ $task->due_date ? 'Due '.$task->due_date->format('M j, Y') : 'No due date' }} · Updated {{ $task->updated_at->diffForHumans() }}</p>
</div>
<x-status-badge :status="$task->status" />
</article>
@empty
<div class="py-10 text-center">
<p class="font-medium">A clear space for your next step.</p>
<p class="mt-2 text-sm text-slate-500">Create a task to start tracking your work.</p>
<x-button class="mt-5" :href="route('tasks.create')">Create a task</x-button>
</div>
@endforelse
</div>
