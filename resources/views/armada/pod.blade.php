@extends('layouts.armada')

@section('title', 'Scan PoD // Bukti Terima Surat Jalan')

@section('content')
    @php
        $badgeTone = [
            'accent' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/40',
        ];

        $metaTone = [
            'soft' => 'text-line-board',
            'accent' => 'text-accent',
        ];

        $checklist = $steps[0]['checklist'] ?? [];
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-5 px-3 pb-14"
        x-data="armadaPod(@js($steps))">

        {{-- Target surat jalan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-line-board/60 pb-2.5">
                <div class="flex items-center gap-1.5">
                    <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-success-deep" />
                    <p class="gpa-meta font-bold text-ink">TARGET PENYERAHAN #{{ $target['sj'] }}</p>
                </div>
                <span class="rounded bg-accent px-2 py-0.5 text-[9px] font-bold leading-3 gpa-meta text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                    {{ $target['status'] }}
                </span>
            </header>

            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-0.5 rounded-lg bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/40">
                    <p class="gpa-micro-bold text-ink-body">{{ $target['client_label'] }}</p>
                    <p class="text-xs font-semibold leading-4 text-ink">{{ $target['client'] }}</p>
                    <p class="gpa-note text-ink-quiet">{{ $target['client_legal'] }}</p>
                </div>

                <div class="flex flex-col gap-0.5 rounded-lg bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/40">
                    <p class="gpa-micro-bold text-ink-body">{{ $target['dock_label'] }}</p>
                    <p class="text-xs font-semibold leading-4 text-ink">{{ $target['dock'] }}</p>
                    <p class="gpa-note text-ink-quiet">{{ $target['dock_area'] }}</p>
                </div>

                <div class="flex flex-col gap-0.5 rounded-lg bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/40">
                    <p class="gpa-micro-bold text-ink-body">{{ $target['weight_label'] }}</p>
                    <p class="flex items-baseline gap-1">
                        <span class="font-inter text-lg font-bold leading-6 text-ink">{{ $target['weight'] }}</span>
                        <span class="gpa-meta font-bold text-success-deep">{{ $target['weight_unit'] }}</span>
                    </p>
                    <p class="gpa-note text-ink-body">{{ $target['commodity'] }}</p>
                    <p class="gpa-note text-ink-body">{{ $target['commodity_tail'] }}</p>
                </div>

                <div class="flex flex-col items-start gap-0.5 rounded-lg bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/40">
                    <p class="gpa-micro-bold text-ink-body">{{ $target['pic_label'] }}</p>
                    <p class="text-xs font-semibold leading-4 text-ink">{{ $target['pic'] }}</p>
                    <span class="rounded bg-brand px-1.5 py-0.5 text-[9px] font-semibold leading-3 gpa-meta text-accent">
                        {{ $target['pic_role'] }}
                    </span>
                </div>
            </div>
        </section>

        @foreach ($steps as $step)
            <section @class([
                'flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card',
                'scroll-mt-16' => true,
            ]) id="pod-step-{{ $step['key'] }}" x-ref="step-{{ $step['key'] }}">

                <header class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink">
                            <span class="text-[11px] font-bold leading-[14px] text-white gpa-meta">{{ $step['step'] }}</span>
                        </span>
                        <h2 class="text-lg font-semibold leading-6 text-ink">
                            {{ $step['title'] }}<br>{{ $step['title_tail'] }}
                        </h2>
                    </div>

                    <span class="shrink-0 rounded px-2 py-0.5 text-[9px] font-bold leading-3 gpa-meta {{ $badgeTone[$step['badge_tone']] }}">
                        {{ str_replace(' ', '<br>', $step['badge']) }}
                    </span>
                </header>

                @if ($step['key'] === 'manifest')
                    <div class="flex min-h-[243px] flex-col justify-between gap-3 overflow-hidden rounded-xl bg-brand-deep p-3 outline outline-2 -outline-offset-2 outline-accent-deep">
                        <div class="flex flex-1 items-center justify-center">
                            <div class="relative h-[183px] w-[244px] rounded-lg outline outline-2 -outline-offset-2 outline-white/40">
                                <span class="absolute -top-2 left-2.5 bg-brand-deep px-1 text-[9px] font-semibold leading-3 gpa-meta text-accent">
                                    {{ $step['viewfinder_label'] }}
                                </span>
                                <span class="absolute -left-0.5 -top-0.5 h-4 w-4 border-l-2 border-t-2 border-accent" aria-hidden="true"></span>
                                <span class="absolute -right-0.5 -top-0.5 h-4 w-4 border-r-2 border-t-2 border-accent" aria-hidden="true"></span>
                                <span class="absolute -bottom-0.5 -left-0.5 h-4 w-4 border-b-2 border-l-2 border-accent" aria-hidden="true"></span>
                                <span class="absolute -bottom-0.5 -right-0.5 h-4 w-4 border-b-2 border-r-2 border-accent" aria-hidden="true"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="flex flex-col gap-0.5">
                                @foreach ($step['meta'] as $meta)
                                    <span class="rounded bg-ink/60 px-2 py-0.5 text-[9px] font-semibold leading-3 gpa-meta {{ $metaTone[$meta['tone']] }}">
                                        {{ $meta['text'] }}
                                    </span>
                                @endforeach
                            </div>
                            <p class="flex items-center gap-1">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                <span class="text-[9px] font-semibold leading-3 gpa-meta text-accent">{{ $step['status'] }}</span>
                            </p>
                        </div>

                        <div class="flex flex-1 items-center justify-center" x-show="!manifestCaptured()" x-cloak>
                            <div class="flex flex-col items-center gap-1">
                                <x-gpa.icon :name="$step['placeholder_icon']" class="h-8 w-8 text-accent/60" />
                                <p class="px-4 text-center text-[11px] font-medium leading-[14px] text-white gpa-meta">
                                    {{ $step['placeholder_title'] }}
                                </p>
                                <p class="px-4 text-center text-[9px] font-semibold leading-3 text-center gpa-meta text-success-soft">
                                    {{ $step['placeholder_hint'] }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 rounded-lg bg-ink/70 px-2.5 py-1.5"
                            x-show="manifestCaptured()" x-cloak>
                            <p class="text-[9px] font-semibold leading-3 gpa-meta">
                                <span class="text-white">STATUS: </span>
                                <span class="text-accent">{{ $step['status'] }}</span>
                            </p>
                            <p class="text-[9px] font-semibold leading-3 gpa-meta text-line-board">{{ $step['subject'] }}</p>
                        </div>
                    </div>

                    <button type="button" @click="capture('manifest')"
                        :class="manifestCaptured()
                            ? 'bg-brand text-line-board outline outline-1 -outline-offset-1 outline-success-deep'
                            : 'bg-ink text-white outline outline-1 -outline-offset-1 outline-success-deep'"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-lg gpa-body shadow-sub transition-opacity hover:opacity-90">
                        <x-gpa.icon :name="$step['action_icon']" class="h-4 w-4 shrink-0" />
                        {{ $step['action'] }}
                    </button>

                    <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                        <p class="text-[9px] font-bold uppercase leading-3 text-ink gpa-meta">
                            {{ $step['checklist_title'] }}
                        </p>

                        @foreach ($checklist as $index => $item)
                            <label class="flex items-start gap-2">
                                <input type="checkbox" class="gpa-check mt-0.5"
                                    :checked="manifestCaptured() || {{ $index }} === 0"
                                    @change="run('Verifikasi Surat Jalan', '{{ $item }}', 'info')">
                                <span class="text-xs leading-4 text-ink">{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($step['key'] === 'dock')
                    <div class="flex flex-col gap-1 rounded-xl bg-ink p-2.5 outline outline-1 -outline-offset-1 outline-success-deep">
                        <div class="flex items-center justify-between gap-2">
                            <p class="flex items-center gap-1">
                                <x-gpa.icon name="map-pin" class="h-2.5 w-2.5 shrink-0 text-accent" />
                                <span class="text-[9px] font-bold leading-3 gpa-meta text-accent">{{ $step['telemetry'][0]['label'] }}</span>
                            </p>
                            <span class="rounded bg-accent px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-meta text-ink">
                                {{ $step['telemetry'][1]['label'] }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-2">
                            @foreach ($step['telemetry_meta'] as $item)
                                <p class="text-[8.5px] font-semibold leading-3 gpa-meta text-line-board">
                                    {{ $item['label'] }}<br>{{ $item['label_tail'] }}
                                </p>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex min-h-[188px] flex-col justify-between overflow-hidden rounded-xl bg-brand-deep p-3 outline outline-2 -outline-offset-2 outline-ink-strong">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded bg-ink/60 px-2 py-0.5 text-[9px] font-semibold leading-3 gpa-meta text-accent">
                                {{ $step['viewfinder_label'] }}
                            </span>
                            <span class="rounded bg-brand px-2 py-0.5 text-[9px] font-semibold leading-3 gpa-meta text-white outline outline-1 -outline-offset-1 outline-accent-deep">
                                {{ $step['dock_chip'] }}
                            </span>
                        </div>

                        <div class="flex flex-1 items-center justify-center" x-show="!dockCaptured()" x-cloak>
                            <div class="flex flex-col items-center gap-1">
                                <x-gpa.icon :name="$step['placeholder_icon']" class="h-8 w-8 text-accent-deep" />
                                <p class="px-4 text-center text-[11px] font-medium leading-[14px] text-white gpa-meta">
                                    {{ $step['placeholder_title'] }}
                                </p>
                                <p class="px-4 text-center text-[9px] font-semibold leading-3 text-center gpa-meta text-success-soft">
                                    {{ $step['placeholder_hint'] }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2" x-show="dockCaptured()" x-cloak>
                            <p class="text-[10px] font-semibold leading-5 gpa-meta text-accent">
                                {{ $step['status'] }}
                            </p>
                            <p class="text-right text-[10px] font-semibold leading-5 gpa-meta text-accent">
                                {{ $step['status_tail'] }}<br>{{ $step['status_tail_note'] }}
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="capture('dock')"
                        :class="dockCaptured()
                            ? 'bg-brand text-line-board outline outline-1 -outline-offset-1 outline-success-deep'
                            : 'bg-ink text-white outline outline-1 -outline-offset-1 outline-success-deep'"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-lg gpa-body shadow-sub transition-opacity hover:opacity-90">
                        <x-gpa.icon :name="$step['action_icon']" class="h-4 w-4 shrink-0" />
                        {{ $step['action'] }}
                    </button>
                @else
                    <div class="flex min-h-[160px] flex-col justify-between gap-2 overflow-hidden rounded-xl bg-surface-shell p-3 outline outline-2 -outline-offset-2 outline-line-board">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[9px] font-semibold leading-3 gpa-meta text-ink-quiet">{{ $step['canvas_label'] }}</p>
                            <button type="button" @click="clearSignature()"
                                class="flex items-center gap-0.5 text-[9px] font-semibold leading-3 gpa-meta text-danger">
                                <x-gpa.icon name="refresh" class="h-2.5 w-2.5" />
                                {{ $step['canvas_clear'] }}
                            </button>
                        </div>

                        <div class="flex flex-1 items-center justify-center py-2">
                            <div class="relative h-20 w-full overflow-hidden">
                                <div class="absolute left-4 top-7 h-7 w-[209px] rounded-sm border-2 border-brand"></div>
                                <p x-show="!signed()" x-cloak
                                    class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-[9px] font-semibold leading-3 gpa-meta text-ink-body/70">
                                    Tanda tangan penerima muncul di area ini
                                </p>
                                <p x-show="signed()" x-cloak
                                    class="absolute inset-x-0 top-1/2 -translate-y-1/2 truncate px-2 text-center font-inter text-lg italic leading-7 text-brand"
                                    x-text="signature"></p>
                            </div>
                        </div>

                        <div class="relative border-t border-line-board pt-1.5">
                            <p class="text-[9px] font-semibold leading-3 gpa-meta text-ink-body">
                                X<br><span class="tracking-[0.3em]">..............................................................</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-0.5 rounded-lg bg-surface-pill/50 p-2.5 outline outline-1 -outline-offset-1 outline-line-board">
                        <p class="gpa-micro-bold text-ink-body">{{ $step['identity_label'] }}</p>
                        <p class="text-sm font-bold leading-5 text-ink">{{ $step['identity_name'] }}</p>
                        <p class="text-[11px] font-medium leading-[14px] gpa-meta text-ink-body">{{ $step['identity_role'] }}</p>

                        <button type="button" @click="sign('{{ $step['identity_name'] }}')"
                            class="mt-1.5 flex h-10 w-full items-center justify-center gap-1.5 rounded-lg gpa-meta font-bold shadow-sub transition-opacity hover:opacity-90"
                            :class="signed() ? 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep' : 'bg-brand text-accent'">
                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                            <span x-text="signed() ? 'Tanda Tangan Tersimpan' : 'Tanda Tangan di Kanvas'"></span>
                        </button>
                    </div>
                @endif
            </section>
        @endforeach

        {{-- Status kondisi muatan dan retur --}}
        <section class="flex flex-col gap-3.5 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center gap-2 border-b border-line-board/60 pb-2">
                <x-gpa.icon name="clipboard" class="h-4 w-4 shrink-0 text-success-deep" />
                <h2 class="text-lg font-semibold leading-6 text-ink">{{ $condition_title }}</h2>
            </header>

            <div class="flex flex-col gap-2.5">
                @foreach ($conditions as $condition)
                    <label @class([
                        'flex cursor-pointer items-start gap-3 rounded-xl p-3 transition-colors',
                        'bg-accent/20 outline outline-2 -outline-offset-2 outline-success-deep' => $condition['tone'] === 'accent',
                        'bg-surface-shell outline outline-1 -outline-offset-1 outline-line-board' => $condition['tone'] !== 'accent',
                    ])>
                        <input type="radio" name="pod-condition" value="{{ $condition['key'] }}" class="sr-only"
                            @checked($loop->first)
                            @change="selectCondition('{{ $condition['key'] }}')">

                        <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center">
                            <span @class([
                                'flex h-4 w-4 items-center justify-center rounded-full border',
                                'border-ink bg-ink' => $loop->first,
                                'border-ink-quiet bg-surface' => ! $loop->first,
                            ])>
                                <span @class([
                                    'h-1.5 w-1.5 rounded-full bg-white' => $loop->first,
                                ])></span>
                            </span>
                        </span>

                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span class="flex items-center justify-between gap-2">
                                <span @class([
                                    'text-xs font-bold leading-4',
                                    'text-ink' => $condition['title_tone'] === 'ink',
                                    'text-warning-deep' => $condition['title_tone'] !== 'ink',
                                ])>{{ $condition['title'] }}</span>

                                <span @class([
                                    'shrink-0 rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-meta',
                                    'bg-accent text-success-deep outline outline-1 -outline-offset-1 outline-success-deep' => $condition['badge_tone'] === 'success',
                                    'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/40' => $condition['badge_tone'] === 'warning',
                                ])>{{ $condition['badge'] }}</span>
                            </span>

                            <span class="text-[9px] font-semibold leading-3 gpa-meta text-ink-quiet">{{ $condition['description'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            <footer class="flex items-center justify-between gap-3 border-t border-line-board/60 pt-2">
                <p class="text-xs leading-4 text-ink-body">{{ $closure['label'] }}</p>
                <button type="button" @click="callHub()"
                    class="flex items-center gap-1 rounded-lg bg-surface-pill px-2.5 py-1">
                    <x-gpa.icon name="headset" class="h-3 w-3 shrink-0 text-success-deep" />
                    <span class="text-[11px] font-bold leading-[14px] gpa-meta text-ink-strong">
                        {{ $closure['hub_label'] }}<br>{{ $closure['hub_phone'] }}
                    </span>
                </button>
            </footer>
        </section>

        {{-- Lock PoD --}}
        <section class="flex flex-col gap-2 pt-2">
            <button type="button" @click="lockPod()"
                class="flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-accent px-4 shadow-pop outline outline-2 -outline-offset-2 outline-ink transition-opacity hover:opacity-90"
                :class="canLock() ? '' : 'opacity-70'">
                <x-gpa.icon name="lock" class="h-4 w-4 shrink-0 text-ink-strong" />
                <span class="text-center text-base font-bold leading-6 text-ink-strong">
                    {{ $closure['action'] }}<br>{{ $closure['action_tail'] }}
                </span>
            </button>

            <p x-show="!canLock()" x-cloak
                class="text-center text-[9px] font-semibold leading-3 gpa-meta text-danger">
                Langkah wajib belum lengkap: <span x-text="blockers().join(', ')"></span>
            </p>

            <p class="flex items-center justify-center gap-1 text-center text-[9px] font-semibold leading-3 gpa-meta text-ink-body">
                <x-gpa.icon name="bolt" class="h-2.5 w-2.5 shrink-0 text-success-deep" />
                {{ $closure['note'] }}
            </p>
        </section>
    </div>
@endsection