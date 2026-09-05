@props(['href' => null, 'secondary' => false])
@php($classes = 'inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors '.($secondary ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' : 'bg-brand text-white hover:bg-blue-800'))
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type'=>'submit'])->class([$classes]) }}>{{ $slot }}</button>
@endif
