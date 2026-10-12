@extends('layouts.armada')

@section('title', 'Scan PoD // Bukti Terima Pengantaran')

@section('content')
    @php
        $badgeTone = [
            'accent'  => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/40',
        ];

        $metaTone = [
            'soft'   => 'text-line-board',
            'accent' => 'text-accent',
        ];

        $checklist = $steps[0]['checklist'] ?? [];
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-4 px-3 pb-24"
        x-data="armadaPod(@js($steps))">

        {{-- Target surat jalan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-shell pb-2.5">
                <p class="gpa-meta font-bold text-ink">TARGET PENYERAHAN #{{ $target['sj'] }}</p>
                <span class="rounded bg-accent px-2 py-0.5 text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                    {{ $target['status'] }}
                </span>
            </header>

            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">{{ $target['client_label'] }}</p>
                    <p class="text-xs font-bold text-ink">{{ $target['client'] }}</p>
                    <p class="gpa-note text-[11px] text-ink-body">{{ $target['client_legal'] }}</p>
                </div>

                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">{{ $target['dock_label'] }}</p>
                    <p class="text-xs font-bold text-ink">{{ $target['dock'] }}</p>
                    <p class="gpa-note text-[11px] text-ink-body">{{ $target['dock_area'] }}</p>
                </div>

                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">{{ $target['weight_label'] }}</p>
                    <p class="flex items-baseline gap-1">
                        <span class="font-inter text-xl font-bold text-ink">{{ $target['weight'] }}</span>
                        <span class="font-mono text-xs font-bold text-success-deep">{{ $target['weight_unit'] }}</span>
                    </p>
                    <p class="gpa-note text-[11px] text-ink-body">{{ $target['commodity'] }} {{ $target['commodity_tail'] }}</p>
                </div>

                <div class="flex flex-col items-start gap-1 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">{{ $target['pic_label'] }}</p>
                    <p class="text-xs font-bold text-ink">{{ $target['pic'] }}</p>
                    <span class="rounded bg-brand px-2 py-0.5 text-[9px] font-semibold text-accent">
                        {{ $target['pic_role'] }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Status pengiriman --}}
        <section class="flex flex-col gap-2 rounded-2xl bg-accent/15 p-3.5 outline outline-1 -outline-offset-1 outline-accent/40">
            <header class="flex items-center gap-2">
                <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-accent-deep" />
                <h2 class="text-xs font-bold text-ink uppercase tracking-wider">STATUS PENGIRIMAN</h2>
            </header>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 text-[10px] font-bold text-ink shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-success-deep animate-pulse" aria-hidden="true"></span>
                    {{ $target['status'] }}
                </span>
                <span class="text-xs font-medium text-ink-body">Lokasi: {{ $target['dock'] }}</span>
            </div>
        </section>

        {{-- Form submission asli untuk PoD --}}
        @if (!empty($target['order_id']))
            <form id="pod-submit-form" method="POST" action="{{ route('armada.pod.store', $target['order_id']) }}" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="hidden" name="condition" id="hidden-condition" x-model="condition">
                <input type="hidden" name="signature" id="hidden-signature" x-model="signature">
                <input type="hidden" name="received_by_name" :value="signature || '{{ $target['pic'] }}'">
                <input type="file" name="manifest_photo" id="photo-input-manifest" accept="image/*" @change="handlePhotoUpload($event, 'manifest')">
                <input type="file" name="dock_photo" id="photo-input-dock" accept="image/*" @change="handlePhotoUpload($event, 'dock')">
            </form>
        @endif

        {{-- Serah terima di dock checklist --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center gap-2 border-b border-surface-shell pb-2">
                <h2 class="text-base font-bold text-ink">CHECKLIST SERAH TERIMA DI LOKASI</h2>
            </header>

            <div class="flex flex-col gap-2">
                <p class="text-xs text-ink-body">Pastikan poin-poin berikut telah dicocokkan bersama perwakilan penerima:</p>

                <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'PIC penerima hadir di lokasi', 'info')">
                        <span class="text-xs leading-4 text-ink">PIC penerima hadir dengan identitas & penugasan jelas</span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'Cap basah stempel & tanda tangan', 'info')">
                        <span class="text-xs leading-4 text-ink">Cap basah stempel penerima & paraf penanggung jawab</span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'Muatan diserahkan utuh', 'info')">
                        <span class="text-xs leading-4 text-ink">Muatan sayur segar diserahkan utuh sesuai kemasan krat</span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'Berat timbangan sesuai toleransi', 'info')">
                        <span class="text-xs leading-4 text-ink">Timbangan cocok sesuai toleransi susut metrologi (≤ 2%)</span>
                    </label>
                </div>
            </div>
        </section>

        {{-- Tiga langkah wajib penangkapan PoD --}}
        @foreach ($steps as $step)
            <section @class([
                'flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card scroll-mt-16',
            ]) id="pod-step-{{ $step['key'] }}" x-ref="step-{{ $step['key'] }}">

                <header class="flex items-center justify-between gap-3 border-b border-surface-shell pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink text-white font-mono text-xs font-bold">
                            {{ $step['step'] }}
                        </span>
                        <h2 class="text-base font-bold text-ink">
                            {{ $step['title'] }} {{ $step['title_tail'] }}
                        </h2>
                    </div>

                    <span class="shrink-0 rounded px-2 py-0.5 text-[9px] font-bold {{ $badgeTone[$step['badge_tone']] }}">
                        {{ $step['badge'] }}
                    </span>
                </header>

                @if ($step['key'] === 'manifest')
                    <div class="flex min-h-[200px] flex-col justify-between gap-3 overflow-hidden rounded-xl bg-brand p-3 text-white outline outline-2 -outline-offset-2 outline-accent">
                        {{-- Image preview if uploaded --}}
                        <template x-if="previews.manifest">
                            <div class="relative flex h-48 w-full items-center justify-center overflow-hidden rounded-lg bg-black">
                                <img :src="previews.manifest" alt="Surat Jalan" class="h-full w-full object-contain">
                                <span class="absolute top-2 right-2 rounded bg-success-deep px-2 py-0.5 text-[10px] font-bold text-white">TERSIMPAN</span>
                            </div>
                        </template>

                        <div x-show="!previews.manifest" class="flex flex-1 flex-col items-center justify-center gap-2 py-4">
                            <x-gpa.icon :name="$step['placeholder_icon']" class="h-10 w-10 text-accent" />
                            <p class="text-center font-bold text-xs text-white">{{ $step['placeholder_title'] }}</p>
                            <p class="text-center text-[10px] text-accent">{{ $step['placeholder_hint'] }}</p>
                        </div>

                        <div class="flex items-center justify-between text-[10px] font-mono">
                            <span class="rounded bg-black/40 px-2 py-0.5 text-accent">{{ $step['viewfinder_label'] }}</span>
                            <span class="rounded bg-black/40 px-2 py-0.5" x-text="manifestCaptured() ? 'STATUS: FOTO SIAP' : 'STATUS: MENUNGGU FOTO'"></span>
                        </div>
                    </div>

                    <button type="button" @click="capture('manifest')"
                        :class="manifestCaptured()
                            ? 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep font-bold'
                            : 'bg-ink text-white font-bold'"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-xl text-xs uppercase tracking-wider shadow-sub transition-transform active:scale-[0.98]">
                        <x-gpa.icon :name="$step['action_icon']" class="h-4 w-4 shrink-0" />
                        <span x-text="manifestCaptured() ? 'Ganti Foto Surat Jalan' : '{{ $step['action'] }}'"></span>
                    </button>

                    <div class="flex flex-col gap-1.5 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                        <p class="text-[10px] font-bold uppercase text-ink-quiet">{{ $step['checklist_title'] }}</p>
                        @foreach ($checklist as $item)
                            <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                                <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Verifikasi', '{{ $item }}', 'info')">
                                <span>{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>

                @elseif ($step['key'] === 'dock')
                    <div class="flex flex-col gap-1.5 rounded-xl bg-ink p-3 text-white">
                        <div class="flex items-center justify-between text-[10px] font-mono">
                            <span class="flex items-center gap-1 text-accent">
                                <x-gpa.icon name="map-pin" class="h-3 w-3" />
                                <span>{{ $step['telemetry'][0]['label'] }}</span>
                            </span>
                            <span class="rounded bg-accent px-1.5 py-0.5 text-ink font-bold">{{ $step['telemetry'][1]['label'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-white/70 font-mono">
                            <span>{{ $step['telemetry_meta'][0]['label'] }} {{ $step['telemetry_meta'][0]['label_tail'] }}</span>
                            <span class="text-accent">{{ $step['telemetry_meta'][1]['label'] }} {{ $step['telemetry_meta'][1]['label_tail'] }}</span>
                        </div>
                    </div>

                    <div class="flex min-h-[160px] flex-col justify-between gap-3 overflow-hidden rounded-xl bg-brand p-3 text-white outline outline-2 -outline-offset-2 outline-ink">
                        <template x-if="previews.dock">
                            <div class="relative flex h-48 w-full items-center justify-center overflow-hidden rounded-lg bg-black">
                                <img :src="previews.dock" alt="Muatan Dock" class="h-full w-full object-contain">
                                <span class="absolute top-2 right-2 rounded bg-success-deep px-2 py-0.5 text-[10px] font-bold text-white">TERSIMPAN</span>
                            </div>
                        </template>

                        <div x-show="!previews.dock" class="flex flex-1 flex-col items-center justify-center gap-2 py-4">
                            <x-gpa.icon :name="$step['placeholder_icon']" class="h-10 w-10 text-accent" />
                            <p class="text-center font-bold text-xs text-white">{{ $step['placeholder_title'] }}</p>
                            <p class="text-center text-[10px] text-accent">{{ $step['placeholder_hint'] }}</p>
                        </div>
                    </div>

                    <button type="button" @click="capture('dock')"
                        :class="dockCaptured()
                            ? 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep font-bold'
                            : 'bg-ink text-white font-bold'"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-xl text-xs uppercase tracking-wider shadow-sub transition-transform active:scale-[0.98]">
                        <x-gpa.icon :name="$step['action_icon']" class="h-4 w-4 shrink-0" />
                        <span x-text="dockCaptured() ? 'Ganti Foto Serah Terima Muatan' : '{{ $step['action'] }}'"></span>
                    </button>

                @else
                    {{-- Signature pad / canvas --}}
                    <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-2 -outline-offset-2 outline-line-board">
                        <div class="flex items-center justify-between text-[10px] font-mono">
                            <span class="text-ink-quiet">{{ $step['canvas_label'] }}</span>
                            <button type="button" @click="clearSignature()" class="flex items-center gap-1 text-danger font-bold">
                                <x-gpa.icon name="refresh" class="h-3 w-3" />
                                <span>{{ $step['canvas_clear'] }}</span>
                            </button>
                        </div>

                        <div class="relative flex h-24 w-full items-center justify-center rounded-lg border-2 border-dashed border-line-board bg-white p-2">
                            <p x-show="!signed()" class="text-center text-xs text-ink-quiet font-medium">
                                Klik tombol di bawah untuk membubuhkan konfirmasi tanda tangan penerima
                            </p>
                            <p x-show="signed()" x-cloak class="font-serif text-2xl italic font-bold text-brand" x-text="signature"></p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                        <p class="gpa-micro-bold text-ink-quiet text-[10px]">{{ $step['identity_label'] }}</p>
                        <p class="text-sm font-bold text-ink">{{ $step['identity_name'] }}</p>
                        <p class="text-xs text-ink-body">{{ $step['identity_role'] }}</p>

                        <button type="button" @click="sign('{{ $step['identity_name'] }}')"
                            :class="signed() ? 'bg-accent text-ink font-bold shadow-sub' : 'bg-brand text-accent font-bold shadow-sub'"
                            class="mt-2 flex h-10 w-full items-center justify-center gap-2 rounded-lg text-xs uppercase transition-transform active:scale-[0.98]">
                            <x-gpa.icon name="check" class="h-4 w-4" />
                            <span x-text="signed() ? 'Tanda Tangan Dikonfirmasi' : 'Bubuhkan Tanda Tangan Penerima'"></span>
                        </button>
                    </div>
                @endif
            </section>
        @endforeach

        {{-- Status kondisi muatan & retur --}}
        <section class="flex flex-col gap-3.5 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center gap-2 border-b border-surface-shell pb-2">
                <h2 class="text-base font-bold text-ink">{{ $condition_title }}</h2>
            </header>

            <div class="flex flex-col gap-2.5">
                @foreach ($conditions as $condition)
                    <label @class([
                        'flex cursor-pointer items-start gap-3 rounded-xl p-3 transition-colors',
                        'bg-accent/20 outline outline-2 -outline-offset-2 outline-success-deep' => $condition['key'] === 'clear',
                        'bg-surface-shell outline outline-1 -outline-offset-1 outline-line-board' => $condition['key'] !== 'clear',
                    ])>
                        <input type="radio" name="pod-condition-radio" value="{{ $condition['key'] }}" class="sr-only"
                            @checked($loop->first)
                            @change="selectCondition('{{ $condition['key'] }}')">

                        <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-ink"
                            :class="condition === '{{ $condition['key'] }}' ? 'bg-ink' : 'bg-surface'">
                            <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="condition === '{{ $condition['key'] }}'"></span>
                        </span>

                        <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-ink">{{ $condition['title'] }}</span>
                                <span @class([
                                    'shrink-0 rounded px-1.5 py-0.5 font-mono text-[9px] font-bold',
                                    $condition['badge_tone'] === 'success' ? 'bg-accent text-ink' : 'bg-warning-cream text-warning-caution',
                                ])>{{ $condition['badge'] }}</span>
                            </div>
                            <span class="text-[10px] text-ink-body">{{ $condition['description'] }}</span>
                        </div>
                    </label>
                @endforeach
            </div>

            <footer class="flex items-center justify-between border-t border-surface-shell pt-2.5">
                <p class="text-xs text-ink-body">{{ $closure['label'] }}</p>
                <button type="button" @click="callHub('{{ $closure['hub_phone'] }}')"
                    class="flex items-center gap-1.5 rounded-lg bg-surface-disabled px-2.5 py-1 text-xs font-bold text-ink hover:bg-surface-soft">
                    <x-gpa.icon name="headset" class="h-3.5 w-3.5 text-success-deep" />
                    <span>{{ $closure['hub_label'] }}</span>
                </button>
            </footer>
        </section>

        {{-- Tombol validasi & selesaikan --}}
        <section class="flex flex-col gap-2 pt-2">
            <button type="button" @click="lockPod()"
                :class="canLock() ? 'bg-accent text-ink shadow-pop' : 'bg-surface-disabled text-ink-quiet cursor-not-allowed'"
                class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-mono text-sm font-bold uppercase tracking-wider outline outline-2 -outline-offset-2 outline-ink transition-all active:scale-[0.98]">
                <x-gpa.icon name="lock" class="h-5 w-5 shrink-0" />
                <span>{{ $closure['action'] }} {{ $closure['action_tail'] }}</span>
            </button>

            <p x-show="!canLock()" x-cloak
                class="text-center font-mono text-[10px] font-semibold text-danger">
                Langkah wajib belum lengkap: <span x-text="blockers().join(', ')"></span>
            </p>
        </section>
    </div>
@endsection
