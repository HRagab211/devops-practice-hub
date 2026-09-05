<x-layout title="Overview">
    <div class="mb-9 flex items-center gap-4">
        <x-avatar :user="$user" />
        <div>
            <p class="text-sm text-slate-500">Your workspace</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Hello, {{ $user->name }}.</h1>
        </div>
    </div>
    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        @foreach (['total' => 'All tasks', 'pending' => 'Pending', 'completed' => 'Completed'] as $key => $label)
            <x-card>
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-4 text-4xl font-semibold tabular-nums {{ $key === 'completed' ? 'text-brand' : '' }}">
                    {{ $counts[$key] }}</p>
            </x-card>
        @endforeach
    </div>
    <div class="grid items-start gap-6 lg:grid-cols-[2fr_1fr]">
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Recently updated</h2>
                <x-button :href="route('tasks.create')">New task</x-button>
            </div>
            <x-task-list :tasks="$tasks" />
            <a class="text-sm font-medium text-brand" href="{{ route('tasks.index') }}">View all tasks →</a>
        </x-card>
        <x-card>
            <h2 class="text-lg font-semibold">Your latest report</h2>
            @if ($report)
                <p class="my-4 text-sm text-slate-500">Requested {{ $report->created_at->format('M j, Y · H:i') }}</p>
                <x-status-badge :status="$report->status" />
            @else
                <p class="mt-4 text-sm leading-relaxed text-slate-500">No reports yet. Create a CSV of your tasks
                    whenever you need a copy.</p>
            @endif
            <a class="mt-6 block text-sm font-medium text-brand" href="{{ route('reports.index') }}">Open reports →</a>
        </x-card>
    </div>
</x-layout>
