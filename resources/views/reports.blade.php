<x-layout title="Reports">
    <x-page-heading
        title="Your task reports"
        description="A downloadable copy of your tasks, prepared in the background."
    >
        <form method="POST" action="{{ route('reports.store') }}">
            @csrf
            <x-button>Generate my task report</x-button>
        </form>
    </x-page-heading>
    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                Refresh to see progress. Your download appears when the report is complete.
            </p>
            <x-button secondary :href="route('reports.index')">Refresh</x-button>
        </div>
        <x-report-list :reports="$reports" />
        <div class="mt-5">{{ $reports->links() }}</div>
    </x-card>
</x-layout>
