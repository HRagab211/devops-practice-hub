@props(['title', 'description' => null])
<div class="mb-8 flex flex-wrap items-end justify-between gap-5">
    <div>
        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-3 text-slate-500">{{ $description }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
