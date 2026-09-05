<x-layout title="Profile settings">
    <x-page-heading title="Profile settings" description="Make this workspace yours." />
    <div class="grid items-start gap-6 lg:grid-cols-[3fr_2fr]">
        <x-card>
            <h2 class="mb-6 text-lg font-semibold">Personal details</h2>
            <form class="space-y-6" method="POST" enctype="multipart/form-data" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <x-avatar :user="$user" />
                <x-input name="avatar" label="Profile picture" type="file" accept="image/jpeg,image/png,image/webp" />
                <p class="text-xs text-slate-500">JPEG, PNG, or WebP. Maximum 2 MB.</p>
                @if ($user->avatar_path)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_avatar" value="1" />Remove current picture</label>
                @endif
                <x-input name="name" label="Name" :value="$user->name" required maxlength="255" autocomplete="name" />
                <x-input
                    name="email"
                    label="Email address"
                    type="email"
                    :value="$user->email"
                    required
                    autocomplete="email"
                />
                <div>
                    <label for="bio" class="mb-2 block text-sm font-medium">Short bio (optional)</label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="3"
                        maxlength="500"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5"
                    >{{ old('bio', $user->bio) }}</textarea>
                </div>
                <x-button>Save profile</x-button>
            </form>
        </x-card>
        <x-card>
            <h2 class="mb-2 text-lg font-semibold">Change password</h2>
            <p class="mb-6 text-sm text-slate-500">Use at least 8 characters.</p>
            <form class="space-y-5" method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <x-input
                    name="current_password"
                    label="Current password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
                <x-input
                    name="password"
                    label="New password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                />
                <x-input
                    name="password_confirmation"
                    label="Confirm new password"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <x-button>Change password</x-button>
            </form>
        </x-card>
    </div>
</x-layout>
