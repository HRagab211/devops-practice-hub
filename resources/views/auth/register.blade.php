<x-layout title="Your workspace starts here">
<div class="mx-auto max-w-md py-6">
<x-page-heading title="Your workspace starts here" description="Create an account to keep track of your next steps."/>
<x-card>
<form method="POST" action="{{ route('register') }}" class="space-y-5">
@csrf
<x-input name="name" label="Name" required maxlength="255" autocomplete="name" autofocus/>
<x-input name="email" label="Email address" type="email" required autocomplete="email"/>
<x-input name="password" label="Password (at least 8 characters)" type="password" required minlength="8" autocomplete="new-password"/>
<x-input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password"/>
<x-button class="w-full">Create account</x-button>
<p class="text-center text-sm text-slate-500">Already registered? <a class="text-brand" href="{{ route('login') }}">Log in</a>
</p>
</form>
</x-card>
</div>
</x-layout>
