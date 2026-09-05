<x-layout title="Tasks">
<x-page-heading title="Your tasks" description="One place for what’s next, underway, and done.">
<x-button :href="route('tasks.create')">New task</x-button>
</x-page-heading>
<x-card>
<form class="mb-3 flex flex-wrap items-end gap-3" method="GET" action="{{ route('tasks.index') }}">
<div>
<label class="mb-2 block text-sm font-medium" for="status">Filter by status</label>
<select id="status" name="status" class="rounded-lg border border-slate-300 px-3 py-2.5">
<option value="">All statuses</option>
@foreach(['pending','in_progress','completed'] as $option)<option value="{{ $option }}" @selected($status===$option)>{{ ucfirst(str_replace('_',' ',$option)) }}</option>
@endforeach
</select>
</div>
<x-button secondary>Apply filter</x-button>
</form>
<x-task-list :tasks="$tasks"/>
<div class="mt-5">{{ $tasks->links() }}</div>
</x-card>
</x-layout>
