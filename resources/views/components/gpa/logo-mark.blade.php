@props([
    'class' => 'h-7 w-auto',
])

<img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" {{ $attributes->merge(['class' => $class . ' shrink-0 object-contain']) }}>
