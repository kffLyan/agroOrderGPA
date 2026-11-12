@props([
    'name',
    'id' => null,
    'label' => 'Kata sandi',
    'value' => null,
    'required' => false,
    'autocomplete' => 'new-password',
    'placeholder' => null,
    'hint' => null,
])

@php
    $id = $id ?? 'field-'.$name;
    $message = $errors->first($name);
@endphp

<div x-data="{ visible: false }" class="relative">
    <input :type="visible ? 'text' : 'password'" id="{{ $id }}" name="{{ $name }}"
        value="{{ old($name, $value) }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif autocomplete="{{ $autocomplete }}" spellcheck="false"
        @if ($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge(['class' => 'gpa-control pr-10 font-mono']) }} />

    <button type="button" @click="visible = ! visible"
        class="absolute inset-y-0 right-0 flex w-10 items-center justify-center rounded-r text-ink-subtle transition-colors hover:text-ink"
        :aria-label="visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
        :aria-pressed="visible ? 'true' : 'false'">
        <x-gpa.icon name="eye" class="h-4 w-4" x-show="! visible" x-cloak />
        <x-gpa.icon name="eye-off" class="h-4 w-4" x-show="visible" x-cloak />
    </button>
</div>

@if ($hint && ! $message)
    <p class="gpa-hint">{{ $hint }}</p>
@endif
