<x-layout title="Choose a new password">
<div class="mx-auto max-w-md py-6">
<x-page-heading title="Choose a new password" description="Use at least 8 characters."/>
<x-card>
<form method="POST" action="{{ route('password.update') }}" class="space-y-5">
@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<x-input name="email" label="Email address" type="email" :value="$request->email" required autocomplete="email"/>
<x-input name="password" label="New password" type="password" required minlength="8" autocomplete="new-password"/>
<x-input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password"/>
<x-button class="w-full">Reset password</x-button>
</form>
</x-card>
</div>
</x-layout>
