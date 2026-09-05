<x-layout title="Lab">
    <x-page-heading title="Practice lab" description="Live service checks and recorded application activity.">
        <x-button secondary :href="route('lab')">Refresh checks</x-button>
    </x-page-heading>
    <div class="grid gap-6 md:grid-cols-2">
        <x-card>
            <h2 class="mb-5 text-lg font-semibold">Deployment</h2>
            <dl class="space-y-4">
                @foreach (['version' => 'Version', 'commit' => 'Commit', 'instance' => 'Instance'] as $key => $label)
                    <div class="flex justify-between gap-4">
                        <dt class="text-sm text-slate-500">{{ $label }}</dt>
                        <dd class="font-mono text-sm break-all">{{ config('hub.' . $key) }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>
        <x-card>
            <h2 class="mb-5 text-lg font-semibold">Dependencies</h2>
            @foreach ($checks as $name => $ok)
                <div class="flex justify-between py-2">
                    <span class="text-sm">{{ ucfirst($name) }}</span>
                    <span class="text-sm font-medium {{ $ok ? 'text-brand' : 'text-red-700' }}">{{ $ok ? 'Available' : 'Unavailable' }}</span>
                </div>
            @endforeach
        </x-card>
        <x-card>
            <h2 class="mb-4 text-lg font-semibold">Scheduler heartbeat</h2>
            <p class="font-mono text-sm">
                {{ $heartbeat?->last_ran_at->toIso8601String() ?? 'No heartbeat recorded' }}
            </p>
            <p class="mt-3 text-sm text-slate-500">
                {{ $heartbeat ? 'Last seen ' . $heartbeat->last_ran_at->diffForHumans() : 'Run php artisan schedule:work to begin.' }}
            </p>
        </x-card>
        <x-card>
            <h2 class="mb-4 text-lg font-semibold">Report activity</h2>
            <p class="text-sm leading-relaxed text-slate-500">
                These statuses are recorded by report jobs. Pending means a worker has not started the job. Backend
                connectivity alone does not confirm a running worker.
            </p>
        </x-card>
    </div>
    <x-card class="mt-6">
        <h2 class="text-lg font-semibold">Your recent report jobs</h2>
        <x-report-list :reports="$reports" />
    </x-card>
</x-layout>
