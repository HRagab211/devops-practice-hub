<x-layout title="Welcome">
    <div class="grid items-center gap-12 py-8 lg:grid-cols-2 lg:py-16">
        <div>
            <p class="mb-5 font-mono text-xs uppercase tracking-widest text-brand">Your personal practice workspace</p>
            <h1 class="text-5xl leading-tight font-semibold tracking-tight sm:text-6xl">Small tasks.<br>A working start.
            </h1>
            <p class="mt-6 max-w-md text-lg leading-relaxed text-slate-500">Keep your next steps in one place. Track
                what’s in progress, finish what matters, and take your task history with you.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                @auth<x-button :href="route('dashboard')">Open dashboard</x-button>
                @else
                    <x-button :href="route('register')">Create your workspace</x-button>
                    <x-button :href="route('login')" secondary>Welcome back? Log in</x-button>
                @endauth
            </div>
        </div>
        <x-card class="relative overflow-hidden">
            <div class="absolute inset-y-0 left-0 w-1 bg-brand">
            </div>
            <p class="font-mono text-xs uppercase tracking-widest text-slate-400">A place to make progress</p>
            <div class="mt-8 space-y-8">
                @foreach (['Plan your next step' => 'Give each task a clear title and an optional due date.', 'Make room for progress' => 'Move work from pending to in progress to completed.', 'Keep a record' => 'Generate and download your own task reports.'] as $heading => $body)
                    <div class="border-l-2 border-blue-100 pl-5">
                        <h2 class="font-semibold">{{ $heading }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</x-layout>
