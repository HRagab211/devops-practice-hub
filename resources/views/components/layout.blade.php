@props(['title' => 'DevOps Practice Hub'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }} · DevOps Practice Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-4"
        >Skip to content</a>
    <x-navigation />
    <main id="main" class="mx-auto max-w-6xl px-5 py-10 sm:px-8 sm:py-14">
        @if (session('status'))
            <x-alert>{{ session('status') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif
        <x-validation-errors />
        {{ $slot }}
    </main>
    <footer class="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-5 py-8 text-xs text-slate-500 sm:px-8">
        <span>DevOps Practice Hub</span>
        <span>A little progress, every day.</span>
    </footer>
</body>
</html>
