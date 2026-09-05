<header class="border-b border-slate-200 bg-white">
    <nav
        aria-label="Main navigation"
        class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5 px-5 py-5 sm:px-8"
    >
        <a href="{{ route('home') }}" class="flex items-center gap-3 font-semibold tracking-tight">
            <span aria-hidden="true" class="bg-brand grid size-9 place-items-center rounded-lg font-mono text-white"
                >&gt;_</span>
            <span>DevOps <span class="font-normal text-slate-500">Practice Hub</span> </span>
        </a>
        <div class="flex flex-wrap items-center gap-5 text-sm">
            @auth
                @foreach (['dashboard' => 'Overview', 'tasks.index' => 'Tasks', 'reports.index' => 'Reports', 'profile.edit' => 'Profile'] as $route => $label)
                    <a
                        href="{{ route($route) }}"
                        @if (request()->routeIs(explode('.', $route)[0].'*')) aria-current="page" @endif
                        class="{{ request()->routeIs(explode('.', $route)[0].'*') ? 'font-semibold text-brand' : 'text-slate-600 hover:text-brand' }}"
                    >{{ $label }}</a>
                @endforeach
                @if (config('hub.lab_enabled'))
                    <a class="hover:text-brand text-slate-600" href="{{ route('lab') }}">Lab</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="hover:text-brand text-slate-500">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="font-medium">Log in</a>
                <x-button :href="route('register')">Create account</x-button>
            @endauth
        </div>
    </nav>
</header>
