@props([
    'name',
    'label',
    'id' => null,
    'required' => false,
    'hint' => null,
    'note' => null,
    'class' => '',
])

@php
    $id = $id ?? 'field-'.$name;
    $message = $errors->first($name);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5 '.$class]) }}>
    <label for="{{ $id }}" class="gpa-label">
        {{ $label }}
        @if ($required)
            <span class="text-danger" aria-hidden="true">*</span>
            <span class="sr-only">(wajib diisi)</span>
        @endif
        @if ($note)
            <span class="ml-1 font-normal normal-case tracking-normal text-ink-subtle">({{ $note }})</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint && ! $message)
        <p class="gpa-hint">{{ $hint }}</p>
    @endif

    @if ($message)
        <p class="gpa-error" id="{{ $id }}-error" role="alert">
            <x-gpa.icon name="alert-circle" class="mt-px h-3 w-3 shrink-0" />
            <span>{{ $message }}</span>
        </p>
    @endif
</div>
