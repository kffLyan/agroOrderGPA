@props([
    'name' => 'berkas',
    'label' => 'Unggah Berkas',
    'accept' => '',
    'hint' => null,
    'required' => false,
    'multiple' => true,
    'maxSizeMb' => 8,
    'class' => '',
])

@php
    $inputName = $multiple ? $name.'[]' : $name;
    $hintText = $hint ?? ($multiple
        ? 'PDF, JPG, atau PNG. Maksimal '.$maxSizeMb.' MB per berkas.'
        : 'PDF, JPG, atau PNG. Maksimal '.$maxSizeMb.' MB.');

    $config = json_encode([
        'accept' => $accept,
        'multiple' => (bool) $multiple,
        'maxSizeMb' => (int) $maxSizeMb,
    ], JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
@endphp

<div {{ $attributes->merge(['class' => $class]) }}
    x-data='gpaUpload({!! $config !!})'>

    <input x-ref="input" type="file" name="{{ $inputName }}" class="sr-only"
        @if ($accept) accept="{{ $accept }}" @endif @if ($multiple) multiple @endif
        aria-describedby="{{ $name }}-hint">

    <button type="button" x-on:click="browse()"
        x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="handleDrop($event)"
        class="flex w-full flex-col items-center justify-center gap-1.5 rounded-lg border border-dashed px-4 py-6 text-center transition-colors"
        :class="dragging ? 'border-brand bg-brand-soft' : 'border-line-board bg-surface-muted hover:border-brand hover:bg-brand-soft'">
        <x-gpa.icon name="upload" class="h-5 w-5 text-ink-subtle" />
        <span class="text-xs font-semibold text-ink">
            {{ $label }}@if ($required)<span class="text-danger" aria-hidden="true">*</span>@endif
        </span>
        <span class="gpa-eyebrow">Pilih berkas atau tarik ke area ini</span>
    </button>

    <p id="{{ $name }}-hint" class="mt-1.5 text-2xs leading-relaxed text-ink-subtle">{{ $hintText }}</p>

    <p x-show="error" x-cloak role="alert" class="mt-1.5 text-2xs font-semibold text-danger" x-text="error"></p>

    <ul x-show="hasFiles" x-cloak class="mt-2.5 divide-y divide-line-faint overflow-hidden rounded-lg border border-line">
        <template x-for="(file, index) in files" :key="file.name + file.size">
            <li class="flex items-center gap-2.5 bg-surface px-3 py-2">
                <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-ink-subtle" />
                <span class="min-w-0 flex-1 truncate text-2xs font-semibold text-ink" x-text="file.name"></span>
                <span class="gpa-mono-xs shrink-0 text-ink-subtle" x-text="humanSize(file.size)"></span>
                <button type="button" x-on:click="remove(index)" :aria-label="'Hapus ' + file.name"
                    class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-sm border border-line-soft text-ink-subtle transition-colors hover:border-danger hover:bg-danger-soft hover:text-danger">
                    <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                </button>
            </li>
        </template>
    </ul>
</div>