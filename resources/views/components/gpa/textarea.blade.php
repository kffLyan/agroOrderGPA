@props([
    'name',
    'id' => null,
    'rows' => 3,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
])

@php
    $id = $id ?? 'field-'.$name;
    $message = $errors->first($name);
@endphp

<textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif
    @if ($required) required @endif @if ($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->merge(['class' => 'gpa-control min-h-0 resize-y leading-relaxed']) }}>{{ old($name, $value) }}</textarea>

@if ($hint && ! $message)
    <p class="gpa-hint">{{ $hint }}</p>
@endif
