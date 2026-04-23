@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-slate-900 px-4 py-2.5 text-start text-sm font-semibold text-white transition'
            : 'block w-full rounded-xl px-4 py-2.5 text-start text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
