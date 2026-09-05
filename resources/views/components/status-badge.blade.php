@props(['status'])
<span {{ $attributes->class(['inline-flex rounded-md px-2.5 py-1 text-xs font-medium','bg-blue-50 text-blue-800'=>in_array($status,['completed','in_progress','processing']),'bg-slate-100 text-slate-600'=>in_array($status,['pending']),'bg-red-50 text-red-800'=>$status==='failed']) }}>{{ ucfirst(str_replace('_',' ',$status)) }}</span>
