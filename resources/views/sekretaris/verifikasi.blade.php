@extends('layouts.sekretaris')

@section('title', 'Verifikasi Pesanan')

@section('content')
    <div class="space-y-3" x-data="secretaryVerification(@js($mappedOrders ?? []), 0)"
        @gpa:search.window="query = $event.detail">
        <section class="rounded-2xl bg-surface p-4 sm:p-6 shadow-card ring-1 ring-line-hair">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-1.5 gpa-note font-semibold text-success-deep">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0" />
                        Verifikasi &amp; Otorisasi PO Masuk
                    </p>
                    <h1 class="mt-1 sm:mt-2 font-sans text-xl sm:text-2xl lg:text-3xl font-bold leading-tight sm:leading-10 tracking-[-0.01em] text-ink">
                        Verifikasi Pesanan Masuk &amp; Kesiapan Panen
                    </h1>
                </div>

                <div class="shrink-0 rounded-xl bg-surface-shell px-3.5 py-2 text-right shadow-sub outline outline-1 outline-line-board">
                    <p class="gpa-micro uppercase tracking-[0.5px] text-ink-quiet">Tingkat Kepatuhan Verifikasi</p>
                    <p class="mt-0.5 font-mono text-xs sm:text-sm font-bold tracking-[0.2px] text-success-deep">
                        100% Sesuai Aturan
                    </p>
                </div>
            </div>
        </section>

        <section class="gpa-panel overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        <x-gpa.icon name="package" class="h-[18px] w-[18px] shrink-0 text-success-deep" />
                        Antrean Pesanan Masuk
                    </h2>
                    <span class="shrink-0 rounded-full bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                        {{ count($mappedOrders ?? []) }} Pesanan
                    </span>
                </div>

                <div class="px-6 pb-1">
                    <div class="inline-flex flex-wrap gap-1.5 rounded-lg bg-surface-shell p-1" role="group"
                        aria-label="Saring antrean pesanan">
                        <button type="button" @click="filter = 'all'"
                            :class="filterClass('all')"
                            class="rounded px-2.5 py-1.5 gpa-micro transition-colors">
                            Semua ({{ count($mappedOrders ?? []) }})
                        </button>
                        <button type="button" @click="filter = 'b2b'"
                            :class="filterClass('b2b')"
                            class="rounded px-2.5 py-1.5 gpa-micro transition-colors">
                            B2B Kontrak ({{ $b2bCount ?? 0 }})
                        </button>
                        <button type="button" @click="filter = 'reguler'"
                            :class="filterClass('reguler')"
                            class="rounded px-2.5 py-1.5 gpa-micro transition-colors">
                            Klien Reguler ({{ $regulerCount ?? ($waCount ?? 0) }})
                        </button>
                    </div>
                </div>

                <div class="grid gap-2 p-4 pt-4">
                    @foreach ($mappedOrders ?? [] as $order)
                        <article x-show="matchesFilter(@js($order))" :class="cardClass(@js($order))"
                            @click="select(@js($order['id']))"
                            class="group relative rounded-xl p-3 transition-all cursor-pointer hover:shadow-md">
                            <span x-cloak x-show="isSelected(@js($order['id']))"
                                class="absolute -top-2 right-4 rounded bg-success-deep px-2 py-0.5 gpa-micro-bold text-white shadow-sm">
                                Terpilih Inspeksi
                            </span>

                            <button type="button" @click.stop="select(@js($order['id']))" class="w-full text-left cursor-pointer">
                                <div class="flex items-start justify-between gap-3 pt-2">
                                    <div class="min-w-0">
                                        <p class="gpa-note text-success-deep">{{ $order['id'] }}</p>
                                        <p class="font-sans text-[0.9375rem] font-bold leading-5 text-ink">
                                            {{ $order['client'] }}
                                        </p>
                                        <p class="mt-0.5 text-xs leading-5 text-ink-body">{{ $order['meta'] }}</p>
                                    </div>

                                    <div class="flex flex-col items-end gap-1 shrink-0">
                                        @if (! empty($order['chip']))
                                            <span class="rounded px-1.5 py-0.5 gpa-micro-bold {{ $order['chip']['class'] }}">
                                                {{ $order['chip']['label'] }}
                                            </span>
                                        @else
                                            <x-gpa.icon name="shield" class="h-5 w-5 shrink-0 text-success-deep" />
                                        @endif

                                        @if (!empty($order['is_frozen']))
                                            <span class="rounded bg-danger px-1.5 py-0.5 gpa-micro-bold text-white text-[10px]">
                                                DIBEKUKAN
                                            </span>
                                        @elseif (!empty($order['is_over_limit']))
                                            <span class="rounded bg-danger-soft px-1.5 py-0.5 gpa-micro-bold text-danger-ink text-[10px]">
                                                MELEBIHI PLAFON
                                            </span>
                                        @elseif (!empty($order['is_contract']))
                                            <span class="rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink text-[10px]">
                                                PLAFON AMAN
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-2 grid grid-cols-2 gap-3 border-t border-line-soft pt-2">
                                    <div class="min-w-0">
                                        <p class="gpa-micro text-ink-quiet">{{ $order['queue_commodity_label'] }}</p>
                                        @foreach ($order['queue_commodities'] as $commodity)
                                            <p class="text-xs font-semibold leading-5 {{ $commodity['class'] }}">
                                                {{ $commodity['name'] }}
                                                <span class="gpa-note {{ $commodity['weight_class'] }}">
                                                    ({{ $commodity['weight'] }})
                                                </span>
                                            </p>
                                        @endforeach
                                    </div>

                                    <div class="text-right">
                                        <p class="gpa-micro text-ink-quiet">{{ $order['queue_estimate_label'] }}</p>
                                        <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">
                                            {{ $order['queue_estimate'] }}
                                        </p>
                                        <p class="gpa-note {{ $order['queue_estimate_note']['class'] }}">
                                            {{ $order['queue_estimate_note']['label'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                                    <span
                                        class="inline-flex items-center gap-1 rounded px-2 py-0.5 gpa-micro {{ $order['queue_status']['class'] }}">
                                        <x-gpa.icon :name="$order['queue_status']['icon']" class="h-3 w-3 shrink-0" />
                                        {{ $order['queue_status']['label'] }}
                                    </span>
                                    <span class="gpa-note text-ink-quiet">{{ $order['entered'] }}</span>
                                </div>
                            </button>
                        </article>
                    @endforeach

                    <p x-cloak x-show="visibleCount() === 0"
                        class="rounded-xl border border-dashed border-line-board bg-surface-shell px-3 py-8 text-center">
                        <span class="text-xs font-semibold text-ink">Tidak ada pesanan pada antrean ini.</span>
                        <span class="mt-1 block gpa-note text-ink-quiet">
                            Ubah filter kanal atau pulihkan pesanan yang sudah diproses.
                        </span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft bg-surface-shell px-6 py-3">
                    <p class="gpa-note text-ink-body"
                        x-text="'Menampilkan ' + visibleCount() + ' dari ' + pendingCount() + ' pesanan menunggu verifikasi'">
                    </p>
                    <button type="button" x-cloak x-show="resolved.length > 0" @click="restore()"
                        class="gpa-note text-success-deep underline underline-offset-2 hover:text-success">
                        <span x-text="'Tampilkan ' + resolved.length + ' PO Diproses'"></span>
                    </button>
                </div>
        </section>

        <section x-show="!selected" class="gpa-panel p-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-accent text-ink shadow-sub">
                <x-gpa.icon name="check" class="h-7 w-7 text-ink" />
            </div>
            <h3 class="mt-4 font-sans text-xl font-bold text-ink">Antrean Verifikasi Kosong</h3>
            <p class="mt-1.5 max-w-md mx-auto text-sm text-ink-body">
                Semua pesanan masuk telah selesai diverifikasi atau disetujui. Pesanan yang disetujui otomatis beralih ke tahap menunggu pembayaran klien.
            </p>
        </section>

        <section id="order-inspection" x-show="selected" class="gpa-panel scroll-mt-24 p-6 transition-all duration-300">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft pb-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-surface-pill px-2 py-0.5 gpa-micro-bold text-ink"
                            x-text="selected.source"></span>
                        <span class="gpa-note text-ink-quiet" x-text="selected.po_ref"></span>
                    </div>
                    <h2 class="mt-2 font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink"
                        x-text="selected.order_no"></h2>
                </div>

                <div class="shrink-0 text-right">
                    <span :class="selected.status.class"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 outline outline-1 outline-success-deep gpa-micro-bold">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <span x-text="selected.status.label"></span>
                    </span>
                    <p class="mt-1 gpa-note text-ink-quiet" x-text="selected.created"></p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft md:grid-cols-3">
                <template x-for="block in selected.identity" :key="block.label">
                    <div class="min-w-0">
                        <p class="gpa-micro text-ink-quiet" x-text="block.label"></p>
                        <p class="mt-1 font-sans text-sm font-bold leading-5 text-ink" x-text="block.name"></p>
                        <p class="gpa-note mt-0.5" :class="block.code_class" x-text="block.code"></p>
                        <p class="mt-1 text-xs leading-5 text-ink-body" x-text="block.note"></p>
                    </div>
                </template>
            </div>

            <div class="mt-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        <x-gpa.icon name="gauge" class="h-5 w-[18px] shrink-0 text-success-deep" />
                        Kesiapan Pasokan Sayur Kebun
                    </h3>

                    <span :class="selected.supply_status.class"
                        class="inline-flex items-center gap-1.5 rounded px-2.5 py-1 outline outline-1 outline-success-deep gpa-micro-bold">
                        <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                        <span x-text="selected.supply_status.label"></span>
                    </span>
                </div>

                <div class="gpa-scroll-x mt-3 rounded-xl outline outline-1 outline-line-soft">
                    <table class="w-full border-collapse text-left">
                        <thead class="border-b border-line-soft bg-surface-track">
                            <tr>
                                <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Komoditas &amp; Spesifikasi
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-center gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Permintaan (PO)
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-center gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Stok Sistem GPA
                                </th>
                                <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Rencana Alokasi Pasokan (Harvest + Buffer)
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-right gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Status Validasi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-line-faint">
                            <template x-for="line in selected.allocations" :key="line.name">
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-xs font-bold leading-5 text-ink" x-text="line.name"></p>
                                        <p class="mt-0.5 gpa-note text-ink-quiet" x-text="line.sku"></p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="gpa-meta-lg font-bold tracking-[0.28px] text-ink"
                                            x-text="line.request"></span>
                                        <span class="ml-1 gpa-note text-ink-quiet" x-text="line.request_unit"></span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="gpa-meta-lg font-bold tracking-[0.28px]" :class="line.system_class"
                                            x-text="line.system"></span>
                                        <span class="ml-1 gpa-note text-ink-quiet" x-text="line.system_unit"></span>
                                        <p class="mt-0.5 gpa-note" :class="line.surplus_class" x-text="line.surplus"></p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <template x-for="(step, index) in line.plan" :key="step.label">
                                            <div :class="index === 0 ? '' : 'mt-1.5'">
                                                <div class="flex items-baseline justify-between gap-2">
                                                    <p class="text-xs leading-5 text-ink" x-text="step.label"></p>
                                                    <p class="gpa-note shrink-0 text-ink" x-text="step.qty"></p>
                                                </div>
                                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-surface-pill">
                                                    <div class="h-full rounded-full" :class="step.fill"
                                                        :style="'width: ' + step.percent + '%'"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span :class="line.status.class"
                                            class="inline-flex items-center gap-1 rounded px-2 py-1 gpa-micro-bold">
                                            <x-gpa.icon name="lock" x-show="line.status.icon === 'lock'"
                                                class="h-3 w-3 shrink-0" />
                                            <x-gpa.icon name="alert-triangle" x-show="line.status.icon !== 'lock'"
                                                class="h-3 w-3 shrink-0" />
                                            <span x-text="line.status.label"></span>
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <p class="gpa-note text-ink-quiet">Alokasi stok disinkronkan langsung dengan data persediaan komoditas aktif.</p>
                    <p class="gpa-note text-ink-quiet">Status Pasokan: Tersedia &amp; Aman</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl bg-brand p-4 outline outline-2 outline-accent/50">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink">
                        <x-gpa.icon name="info" class="h-4 w-4 text-accent" />
                    </span>

                    <div class="min-w-0">
                        <template x-if="selected.is_contract">
                            <div>
                                <span class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink">Fasilitas Kontrak B2B (Tempo TOP 30 Hari)</span>
                                <h4 class="mt-2 font-sans text-[0.9375rem] font-bold leading-5 text-white">
                                    Ketentuan Pengiriman Mitra B2B Kontrak:
                                </h4>
                                <p class="mt-1.5 text-sm leading-6 text-line-soft">
                                    Pesanan mitra kontrak dapat <span class="font-semibold text-white underline underline-offset-2">langsung diproses untuk alokasi panen &amp; penerbitan Surat Jalan</span> tanpa menunggu pembayaran lunas di awal. Tagihan diagregasi ke faktur konsolidasi bulanan.
                                </p>
                                <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1">
                                    <li class="flex items-center gap-1.5 gpa-note text-accent">
                                        <span class="h-1 w-1 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        Surat Jalan dapat langsung diterbitkan setelah PO disetujui
                                    </li>
                                    <li class="flex items-center gap-1.5 gpa-note text-accent">
                                        <span class="h-1 w-1 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        Pembayaran sesuai termin jatuh tempo kontrak B2B
                                    </li>
                                </ul>
                            </div>
                        </template>
                        <template x-if="!selected.is_contract">
                            <div>
                                <span class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink">Skema Pembayaran di Muka (Klien Reguler)</span>
                                <h4 class="mt-2 font-sans text-[0.9375rem] font-bold leading-5 text-white">
                                    Ketentuan Penetapan Nilai Tagihan Pesanan:
                                </h4>
                                <p class="mt-1.5 text-sm leading-6 text-line-soft">
                                    Kuantitas pesanan awal
                                    <span class="gpa-meta-lg font-bold tracking-[0.28px] text-accent"
                                        x-text="selected.initial_qty"></span>
                                    menjadi dasar perhitungan total tagihan (grand total). Setelah pesanan disetujui Sekretaris,
                                    <span class="font-semibold text-white underline underline-offset-2">
                                        sistem menerbitkan Invoice Tagihan resmi
                                    </span>
                                    untuk diproses pembayarannya oleh mitra klien.
                                </p>
                                <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1">
                                    <li class="flex items-center gap-1.5 gpa-note text-accent">
                                        <span class="h-1 w-1 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        Surat Jalan diterbitkan Sekretaris setelah pembayaran diverifikasi LUNAS
                                    </li>
                                    <li class="flex items-center gap-1.5 gpa-note text-accent">
                                        <span class="h-1 w-1 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        Penyesuaian komoditas hanya dilakukan melalui mekanisme retur resmi saat serah terima
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Evaluasi Plafon Kredit & Status Piutang Klien B2B --}}
            <template x-if="selected.is_contract">
                <div class="mt-4 rounded-xl border p-4 transition-colors"
                    :class="selected.is_frozen ? 'border-danger bg-danger-soft/20' : (selected.is_over_limit ? 'border-warning-deep bg-warning-soft/30' : 'border-line-soft bg-surface-shell')">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft/80 pb-3">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="wallet" class="h-4 w-4 text-success-deep shrink-0" />
                            <h4 class="font-sans text-xs font-bold text-ink uppercase tracking-wider">
                                Evaluasi Plafon Kredit Tempo &amp; Piutang Klien
                            </h4>
                        </div>
                        <template x-if="selected.is_frozen">
                            <span class="inline-flex items-center gap-1 rounded bg-danger px-2.5 py-0.5 text-2xs font-bold text-white shadow-xs">
                                🚫 AKUN DIBEKUKAN DIREKTUR
                            </span>
                        </template>
                        <template x-if="!selected.is_frozen && selected.is_over_limit">
                            <span class="inline-flex items-center gap-1 rounded bg-danger-soft px-2.5 py-0.5 text-2xs font-bold text-danger-ink">
                                ⚠️ MELEBIHI SISA PLAFON
                            </span>
                        </template>
                        <template x-if="!selected.is_frozen && !selected.is_over_limit">
                            <span class="inline-flex items-center gap-1 rounded bg-success-soft px-2.5 py-0.5 text-2xs font-bold text-success-ink">
                                <x-gpa.icon name="check-circle" class="h-3 w-3" />
                                PLAFON KREDIT AMAN
                            </span>
                        </template>
                    </div>

                    {{-- Metric Grid --}}
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-lg bg-surface p-2.5 outline outline-1 outline-line-soft">
                            <p class="gpa-micro text-ink-quiet">Batas Plafon Kontrak</p>
                            <p class="font-mono text-sm font-bold text-ink mt-0.5" x-text="selected.credit_limit_label || selected.plafon"></p>
                        </div>
                        <div class="rounded-lg bg-surface p-2.5 outline outline-1 outline-line-soft">
                            <p class="gpa-micro text-ink-quiet">Piutang Berjalan</p>
                            <p class="font-mono text-sm font-bold text-ink mt-0.5" x-text="selected.credit_used_label || 'Rp 0'"></p>
                        </div>
                        <div class="rounded-lg bg-surface p-2.5 outline outline-1 outline-line-soft">
                            <p class="gpa-micro text-ink-quiet">Sisa Plafon Tersedia</p>
                            <p class="font-mono text-sm font-bold mt-0.5"
                                :class="selected.credit_available_raw > 0 ? 'text-success-deep' : 'text-danger-ink'"
                                x-text="selected.credit_avail_label || 'Rp 0'"></p>
                        </div>
                        <div class="rounded-lg bg-surface p-2.5 outline outline-1"
                            :class="selected.is_over_limit ? 'outline-danger bg-danger-soft/30' : 'outline-line-soft'">
                            <p class="gpa-micro text-ink-quiet">Nilai PO Saat Ini</p>
                            <p class="font-mono text-sm font-bold mt-0.5"
                                :class="selected.is_over_limit ? 'text-danger-ink' : 'text-ink'"
                                x-text="formatRupiah(getGrandTotal())"></p>
                        </div>
                    </div>

                    {{-- Alert Banner jika Overlimit atau Frozen --}}
                    <template x-if="selected.is_frozen">
                        <div class="mt-3 flex items-start gap-2.5 rounded-lg bg-danger/10 border border-danger/30 p-2.5 text-xs text-danger-ink">
                            <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-danger mt-0.5" />
                            <div>
                                <strong class="font-bold">Akun Plafon Klien Sedang Dibekukan:</strong>
                                <span> Direktur Utama telah mengunci hak pemesanan tempo mitra ini karena tunggakan piutang. Pesanan ini <strong>tidak dapat disahkan</strong> sampai status freeze dibuka oleh Direksi.</span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selected.is_frozen && selected.is_over_limit">
                        <div class="mt-3 flex items-start gap-2.5 rounded-lg bg-warning-soft border border-warning-deep/30 p-2.5 text-xs text-warning-deep">
                            <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-warning-deep mt-0.5" />
                            <div>
                                <strong class="font-bold">Peringatan Defisit Plafon:</strong>
                                <span> Total PO ini melebihi sisa plafon kredit klien sebesar <strong class="font-mono" x-text="selected.over_amount_label"></strong>. Pastikan klien mentransfer pembayaran faktur sebelumnya atau telah memiliki memo SK Dispensasi Direktur sebelum Anda menyetujui.</span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <div
                class="mt-4 grid gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="gpa-micro text-ink-quiet">Subtotal Komoditas</p>
                    <p class="mt-1 gpa-meta-lg font-bold tracking-[0.28px] text-ink" x-text="formatRupiah(getSubtotal())"></p>
                </div>
                <div>
                    <p class="gpa-micro text-ink-quiet">Biaya Logistik Armada Chiller</p>
                    <p class="mt-1 gpa-meta-lg font-bold tracking-[0.28px] text-ink" x-text="formatRupiah(getShipping())"></p>
                    <p class="mt-0.5 gpa-note text-ink-body" x-show="getShipping() === 0">(Bebas Ongkir Franco)</p>
                </div>
                <div>
                    <p class="gpa-micro text-ink-quiet" x-text="'Pajak Pertambahan Nilai (' + (selected.tax_label || '0% DTP') + ')'"></p>
                    <p class="mt-1 gpa-meta-lg font-bold tracking-[0.28px] text-ink" x-text="formatRupiah(getTaxAmount())"></p>
                    <p class="mt-0.5 gpa-note text-ink-body" x-text="getTaxAmount() > 0 ? '(PPN Standar PKP 11%)' : '(Bebas Pajak Produk Pertanian)'"></p>
                </div>
                <div class="rounded-lg bg-surface px-3 py-2 outline outline-1 outline-success-deep/30">
                    <p class="gpa-micro text-ink-quiet">Total Tagihan Invoice</p>
                    <p class="mt-1 gpa-meta-lg font-bold tracking-[0.28px] text-success-deep" x-text="formatRupiah(getGrandTotal())"></p>
                </div>
            </div>

            {{-- Form Penyesuaian Biaya Ongkir oleh Sekretaris --}}
            <div class="mt-4 rounded-xl border border-line-soft bg-surface-shell p-4 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-2">
                    <div>
                        <h4 class="font-sans text-xs font-bold text-ink flex items-center gap-1.5">
                            <x-gpa.icon name="truck" class="h-3.5 w-3.5 text-success-deep" />
                            Penetapan Biaya Ongkir Logistik Armada (Otoritas Sekretaris)
                        </h4>
                        <p class="text-[11px] text-ink-body">Sekretaris dapat menentukan tarif ongkir riil armada berpendingin sebelum invoice diterbitkan.</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-ink-body uppercase font-bold tracking-wider">Ongkir Ditetapkan:</span>
                        <p class="font-mono text-sm font-bold text-success-deep" x-text="formatRupiah(manualShipping)"></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="w-full sm:w-48">
                        <label class="block text-[10px] font-bold text-ink-body mb-1">Nominal Ongkir (Rp):</label>
                        <input type="number" x-model.number="manualShipping" min="0" step="5000"
                            placeholder="0"
                            class="w-full rounded border-line-board bg-surface px-3 py-1.5 text-xs font-mono font-bold text-ink focus:border-brand">
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-ink-body mb-1">Pilihan Cepat Tarif Ongkir:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="setShippingPreset(0)"
                                :class="manualShipping === 0 ? 'bg-brand text-accent font-bold ring-1 ring-accent' : 'bg-surface text-ink border border-line-board hover:bg-surface-muted'"
                                class="rounded px-2.5 py-1 text-2xs font-semibold transition">
                                Rp 0 (Bebas Ongkir Franco)
                            </button>
                            <button type="button" @click="setShippingPreset(50000)"
                                :class="manualShipping === 50000 ? 'bg-brand text-accent font-bold ring-1 ring-accent' : 'bg-surface text-ink border border-line-board hover:bg-surface-muted'"
                                class="rounded px-2.5 py-1 text-2xs font-semibold transition">
                                Rp 50.000 (Lokal Kota)
                            </button>
                            <button type="button" @click="setShippingPreset(75000)"
                                :class="manualShipping === 75000 ? 'bg-brand text-accent font-bold ring-1 ring-accent' : 'bg-surface text-ink border border-line-board hover:bg-surface-muted'"
                                class="rounded px-2.5 py-1 text-2xs font-semibold transition">
                                Rp 75.000 (Standar Bandung Raya)
                            </button>
                            <button type="button" @click="setShippingPreset(150000)"
                                :class="manualShipping === 150000 ? 'bg-brand text-accent font-bold ring-1 ring-accent' : 'bg-surface text-ink border border-line-board hover:bg-surface-muted'"
                                class="rounded px-2.5 py-1 text-2xs font-semibold transition">
                                Rp 150.000 (Antar Kota / Jabar)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-2 sm:bottom-4 z-20 mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 sm:gap-3 rounded-xl border border-line-soft bg-surface/95 p-3 sm:p-4 shadow-xl ring-1 ring-black/5 backdrop-blur-md">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" @click="reject()"
                        class="inline-flex h-10 w-full sm:w-auto justify-center items-center gap-1.5 rounded-lg px-4 text-danger outline outline-1 outline-danger transition-colors hover:bg-danger-soft font-sans text-xs font-bold">
                        <x-gpa.icon name="x" class="h-3 w-3 shrink-0" />
                        Tolak Pesanan
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 sm:justify-end w-full sm:w-auto">
                    <template x-if="selected.is_frozen">
                        <span class="rounded bg-danger-soft px-2.5 py-1 text-xs font-bold text-danger-ink text-center">
                            🚫 Akun Dibekukan Direktur — Persetujuan Dinonaktifkan
                        </span>
                    </template>
                    <button type="button" @click="verify()" :disabled="selected.is_frozen"
                        :class="selected.is_frozen ? 'opacity-40 cursor-not-allowed bg-line-board text-ink-quiet' : 'bg-accent text-ink hover:bg-accent-deep'"
                        class="inline-flex h-11 w-full sm:w-auto justify-center items-center gap-2 rounded-lg px-4 sm:px-6 font-sans text-xs sm:text-sm font-extrabold tracking-[0.35px] shadow-sub transition-colors text-center">
                        <x-gpa.icon name="check" class="h-4 w-4 shrink-0" />
                        Setujui Pesanan &amp; Terbitkan Invoice Tagihan
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection
