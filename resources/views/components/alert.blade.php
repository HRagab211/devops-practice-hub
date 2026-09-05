@props(['type' => 'success'])
<div
    role="status"
    {{ $attributes->class(['mb-6 rounded-lg border p-4 text-sm', 'border-blue-200 bg-blue-50 text-blue-900'=>$type==='success', 'border-red-200 bg-red-50 text-red-800'=>$type==='error']) }}
>
    {{ $slot }}
</div>
