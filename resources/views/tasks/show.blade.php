<x-layout title="Task">
    <div class="mx-auto max-w-3xl" data-task-show data-endpoint="{{ route('tasks.show', $task) }}" aria-busy="true">
        <a class="text-brand text-sm" href="{{ route('tasks.index') }}">← All tasks</a>
        <div class="mt-7 mb-8 flex flex-wrap items-end justify-between gap-5">
            <h1 class="text-3xl font-semibold tracking-tight break-words sm:text-4xl" data-field="title">Loading task…</h1>
            <x-button :href="route('tasks.edit', $task)">Edit task</x-button>
        </div>
        <x-card>
            <div class="flex flex-wrap items-center gap-4">
                <span data-field="status"></span>
                <span class="text-sm text-slate-500" data-field="due"></span>
            </div>
            <p class="mt-7 leading-relaxed break-words whitespace-pre-wrap" data-field="description"></p>
            <p class="mt-8 border-t border-slate-100 pt-5 text-xs text-slate-500" data-field="meta"></p>
        </x-card>
        <form
            class="mt-6"
            method="POST"
            action="{{ route('tasks.destroy',$task) }}"
            data-ajax
            data-confirm="Delete this task? This cannot be undone."
        >
            @csrf
            @method('DELETE')
            <button class="text-sm text-red-700">Delete task</button>
        </form>
    </div>
</x-layout>
