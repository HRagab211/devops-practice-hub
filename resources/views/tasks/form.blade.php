<x-layout :title="$task->exists ? 'Edit task' : 'New task'">
    <div class="mx-auto max-w-2xl">
        <x-page-heading
            :title="$task->exists ? 'Edit task' : 'A new next step'"
            description="Keep it clear. You can always update the details later."
        />
        <x-card>
            <form
                class="space-y-6"
                method="POST"
                action="{{ $task->exists ? route('tasks.update',$task) : route('tasks.store') }}"
            >
                @csrf
                @if ($task->exists) @method('PUT')@endif
                <x-input name="title" label="Title" :value="$task->title" required maxlength="255" />
                <div>
                    <label for="description" class="mb-2 block text-sm font-medium"
                        >Description <span class="text-slate-400">(optional)</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        maxlength="10000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5"
                    >{{ old('description',$task->description) }}</textarea>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium">Status</label>
                        <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                            @foreach (['pending', 'in_progress', 'completed'] as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(old('status', $task->status ?? 'pending') === $status)
                                >
                                    {{ ucfirst(str_replace('_',' ',$status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-input
                        name="due_date"
                        label="Due date (optional)"
                        type="date"
                        :value="$task->due_date?->format('Y-m-d')"
                    />
                </div>
                <div class="flex gap-3">
                    <x-button>{{ $task->exists ? 'Save changes' : 'Create task' }}</x-button>
                    <x-button secondary :href="route('tasks.index')">Cancel</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layout>
