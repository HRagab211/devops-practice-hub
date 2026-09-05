@props(['name', 'label', 'type' => 'text', 'value' => null])
<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if (! in_array($type, ['password', 'file'])) value="{{ old($name,$value) }}" @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->class(['block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-base shadow-xs']) }}
    />
    @error($name)
        <p id="{{ $name }}-error" class="text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
