@props([
    'class' => 'h-7 w-7',
])

<img src="{{ asset('gpaleaves.png') }}" alt="Logo AgroOrder GPA" {{ $attributes->merge(['class' => $class . ' shrink-0 object-contain']) }}>
