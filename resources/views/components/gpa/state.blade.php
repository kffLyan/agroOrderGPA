@props([
    'state' => 'ready',
    'title' => null,
    'description' => null,
    'icon' => 'package',
    'rows' => 3,
    'lines' => 3,
    'retryLabel' => 'Muat Ulang Data',
])

@php
    $isLoading = $state === 'loading';
    $isEmpty = $state === 'empty';
    $isError = $state === 'error';

    $defaultTitles = [
        'empty' => 'Belum ada data',
        'error' => 'Gagal memuat data',
    ];

    $defaultDescriptions = [
        'empty' => 'Data akan muncul di sini setelah tersedia. Periksa kembali nanti atau ubah filter yang aktif.',
        'error' => 'Permintaan gagal diproses. Periksa koneksi Anda lalu coba lagi. Data di layar ini belum dapat dipastikan kebenarannya.',
    ];

    $heading = $title ?? ($isEmpty || $isError ? $defaultTitles[$state] : null);
    $body = $description ?? ($isEmpty || $isError ? $defaultDescriptions[$state] : null);
@endphp

@if ($isLoading)
    <div role="status" aria-live="polite">
        <span class="sr-only">Memuat data, mohon tunggu.</span>
        <x-gpa.skeleton :variant="$rows > 1 ? 'table' : 'lines'" :rows="$rows" :lines="$lines" />
    </div>
@elseif ($isEmpty || $isError)
    <div @if ($isError) role="alert" @else role="status" @endif
        {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-lg border px-4 py-8 text-center md:py-10 '.($isError ? 'border-danger/40 bg-danger-soft/40' : 'border-dashed border-line bg-surface-muted')]) }}>
        <span @class([
            'flex h-10 w-10 items-center justify-center rounded-full',
            'bg-danger-soft text-danger' => $isError,
            'bg-surface text-ink-subtle' => ! $isError,
        ])>
            <x-gpa.icon :name="$isError ? 'alert-circle' : $icon" class="h-5 w-5" />
        </span>

        @if ($heading)
            <p @class([
                'mt-3 text-sm font-semibold',
                'text-danger-ink' => $isError,
                'text-ink' => ! $isError,
            ])>{{ $heading }}</p>
        @endif

        @if ($body)
            <p @class([
                'mt-1.5 max-w-md text-2xs leading-relaxed',
                'text-ink-muted' => ! $isError,
                'text-danger' => $isError,
            ])>{{ $body }}</p>
        @endif

        @if ($isError || isset($action))
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                @isset($action)
                    {{ $action }}
                @endisset

                @if ($isError)
                    <x-gpa.btn type="button" variant="secondary" size="sm" @click="window.location.reload()">
                        <x-slot:icon>
                            <x-gpa.icon name="refresh" />
                        </x-slot:icon>
                        {{ $retryLabel }}
                    </x-gpa.btn>
                @endif
            </div>
        @endif
    </div>
@else
    {{ $slot }}
@endif