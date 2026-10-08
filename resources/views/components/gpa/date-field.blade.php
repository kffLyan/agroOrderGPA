@props([
    'name',
    'label',
    'id' => null,
    'value' => null,
    'required' => false,
    'hint' => null,
    'note' => null,
    'min' => null,
    'max' => null,
    'windows' => [],
    'windowName' => null,
    'windowValue' => null,
    'windowLabel' => 'Jendela Pengambilan',
    'windowHint' => null,
    'class' => '',
])

@php
    $id = $id ?? 'date-'.$name;
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $message = $errorBag->first($name);
    $windowId = $windowName ? 'date-'.$windowName : null;
    $windowMessage = $windowName ? $errorBag->first($windowName) : null;
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

    <div @class(['grid gap-2', 'sm:grid-cols-2' => count($windows) > 0])>
        <div class="relative">
            <x-gpa.icon name="calendar"
                class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
            <input type="date" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
                @if ($min) min="{{ $min }}" @endif @if ($max) max="{{ $max }}" @endif
                @if ($required) required @endif @if ($message) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                class="gpa-control pl-8 gpa-mono-xs">
        </div>

        @if (count($windows) > 0)
            <div class="relative">
                <x-gpa.icon name="clock"
                    class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
                <select id="{{ $windowId }}" name="{{ $windowName }}"
                    @if ($windowMessage) aria-invalid="true" aria-describedby="{{ $windowId }}-error" @endif
                    class="gpa-control appearance-none pl-8 pr-8 gpa-mono-xs">
                    <option value="">Pilih {{ $windowLabel }}</option>
                    @foreach ($windows as $key => $text)
                        <option value="{{ is_int($key) ? $text : $key }}"
                            @selected((string) $windowValue === (string) (is_int($key) ? $text : $key))>{{ $text }}</option>
                    @endforeach
                </select>
                <x-gpa.icon name="chevron-down"
                    class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
            </div>
        @endif
    </div>

    @if ($hint && ! $message && ! $windowMessage)
        <p class="gpa-hint">{{ $hint }}</p>
    @elseif ($windowHint && ! $message && ! $windowMessage)
        <p class="gpa-hint">{{ $windowHint }}</p>
    @endif

    @if ($message)
        <p class="gpa-error" id="{{ $id }}-error" role="alert">
            <x-gpa.icon name="alert-circle" class="mt-px h-3 w-3 shrink-0" />
            <span>{{ $message }}</span>
        </p>
    @endif

    @if ($windowMessage)
        <p class="gpa-error" id="{{ $windowId }}-error" role="alert">
            <x-gpa.icon name="alert-circle" class="mt-px h-3 w-3 shrink-0" />
            <span>{{ $windowMessage }}</span>
        </p>
    @endif
</div>