<x-layout title="Welcome back">
    <div class="mx-auto max-w-md py-6">
        <x-page-heading title="Welcome back" description="Pick up where you left off." />
        <x-card>
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <x-input name="email" label="Email address" type="email" required autocomplete="email" autofocus />
                <x-input name="password" label="Password" type="password" required autocomplete="current-password" />
                <div class="flex flex-wrap justify-between gap-3 text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remember" value="1" />Remember me</label>
                    <a class="text-brand" href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <x-button class="w-full">Log in</x-button>
                <p class="text-center text-sm text-slate-500">
                    New here? <a class="text-brand" href="{{ route('register') }}">Create an account</a>
                </p>
            </form>
        </x-card>
    </div>
</x-layout>
