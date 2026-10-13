<x-layout title="Reports">
    <x-page-heading
        title="Your task reports"
        description="A downloadable copy of your tasks, prepared in the background."
    >
        <form method="POST" action="{{ route('reports.store') }}" data-ajax data-reloads-reports>
            @csrf
            <x-button>Generate my task report</x-button>
        </form>
    </x-page-heading>
    <x-card data-reports data-endpoint="{{ route('reports.index') }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                Progress updates automatically. Your download appears when the report is complete.
            </p>
            <x-button secondary type="button" data-refresh>Refresh</x-button>
        </div>
        <div data-report-list aria-live="polite">
            <p class="py-8 text-sm text-slate-500">Loading your reports…</p>
        </div>
        <div class="mt-5" data-pagination></div>
    </x-card>
</x-layout>
