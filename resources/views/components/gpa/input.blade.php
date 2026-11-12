@props([
    'name',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autocomplete' => null,
    'inputmode' => null,
    'maxlength' => null,
    'prefix' => null,
    'hint' => null,
])

@php
    $id = $id ?? 'field-'.$name;
    $message = $errors->first($name);
    $hasPrefix = filled($prefix);
    $hasSuffix = isset($suffix);
@endphp

<div class="flex items-stretch">
    @if ($hasPrefix)
        <span
            class="inline-flex shrink-0 items-center rounded-l border border-r-0 border-line-strong bg-surface-sunken px-2.5 gpa-mono-xs font-semibold text-ink">{{ $prefix }}</span>
    @endif

    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        value="{{ old($name, $value) }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif @if ($disabled) disabled @endif @if ($readonly) readonly @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        @if ($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge([
            'class' => 'gpa-control '.($hasPrefix ? 'rounded-l-none border-r-0 shadow-none ' : '').($hasSuffix ? 'rounded-r-none border-r-0 shadow-none ' : ''),
        ]) }} />

    @isset($suffix)
        <span class="inline-flex shrink-0 items-stretch">{{ $suffix }}</span>
    @endisset
</div>

@if ($hint && ! $message)
    <p class="gpa-hint">{{ $hint }}</p>
@endif
