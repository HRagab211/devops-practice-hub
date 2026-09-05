<x-layout title="Reset your password">
<div class="mx-auto max-w-md py-6">
<x-page-heading title="Reset your password" description="Enter your email address and we’ll send a reset link."/>
<x-card>
<form method="POST" action="{{ route('password.email') }}" class="space-y-5">
@csrf
<x-input name="email" label="Email address" type="email" required autocomplete="email" autofocus/>
<x-button class="w-full">Send reset link</x-button>
<a class="block text-center text-sm text-brand" href="{{ route('login') }}">Back to login</a>
</form>
</x-card>
</div>
</x-layout>
