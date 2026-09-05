@props(['user'])
@if ($user->avatarUrl())
    <img
        src="{{ $user->avatarUrl() }}"
        alt="{{ $user->name }}'s profile picture"
        class="size-14 shrink-0 rounded-full object-cover"
    />
@else
    <span
        aria-label="{{ $user->name }}'s initials"
        class="text-brand grid size-14 shrink-0 place-items-center rounded-full bg-blue-100 text-lg font-semibold uppercase"
    >{{ $user->initials() }}</span>
@endif
