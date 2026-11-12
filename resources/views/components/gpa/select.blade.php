@props([
    'name',
    'options' => [],
    'id' => null,
    'selected' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $id = $id ?? 'field-'.$name;
    $message = $errors->first($name);
    $current = old($name, $selected);
@endphp

<select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif @if ($disabled) disabled @endif
    @if ($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    {{ $attributes->merge(['class' => 'gpa-control cursor-pointer pr-8']) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
    @endforeach
</select>

@if ($hint && ! $message)
    <p class="gpa-hint">{{ $hint }}</p>
@endif
