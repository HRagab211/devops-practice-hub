<x-layout :title="$task->title">
    <div class="mx-auto max-w-3xl">
        <a class="text-brand text-sm" href="{{ route('tasks.index') }}">← All tasks</a>
        <div class="mt-7">
            <x-page-heading :title="$task->title">
                <x-button :href="route('tasks.edit', $task)">Edit task</x-button>
            </x-page-heading>
        </div>
        <x-card>
            <div class="flex flex-wrap items-center gap-4">
                <x-status-badge :status="$task->status" />
                <span class="text-sm text-slate-500">{{ $task->due_date ? 'Due '.$task->due_date->format('M j, Y') : 'No due date' }}</span>
            </div>
            <p class="mt-7 leading-relaxed break-words whitespace-pre-wrap">
                {{ $task->description ?: 'No description added.' }}
            </p>
            <p class="mt-8 border-t border-slate-100 pt-5 text-xs text-slate-500">
                Created {{ $task->created_at->format('M j, Y') }} · Updated {{ $task->updated_at->diffForHumans() }}
            </p>
        </x-card>
        <form
            class="mt-6"
            method="POST"
            action="{{ route('tasks.destroy',$task) }}"
            data-confirm="Delete this task? This cannot be undone."
        >
            @csrf
            @method('DELETE')
            <button class="text-sm text-red-700">Delete task</button>
        </form>
    </div>
</x-layout>
