@extends('layouts.sekretaris')

@section('title', 'Verifikasi Pesanan')

@section('content')
    <div class="space-y-6" x-data="secretaryVerification(@js($mappedOrders ?? []), 0)"
        @gpa:search.window="query = $event.detail">
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-line-soft px-6 py-6">
                <div class="min-w-0">
                    <h1 class="font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-brand">
                        Verifikasi Pesanan Masuk &amp; Kesiapan Panen
                    </h1>
                    <p class="mt-2 max-w-4xl text-sm leading-[1.4rem] text-ink-body">
                        Pemeriksaan pesanan klien, kepastian stok panen kebun, dan persetujuan resmi sebelum jadwal panen dimulai.
                    </p>
                </div>

                <div class="shrink-0 rounded-xl bg-surface px-4 py-2 text-right shadow-sub outline outline-1 outline-line-board">
                    <p class="gpa-micro text-ink-quiet">Tingkat Kepatuhan Verifikasi</p>
                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-success-deep">
                        100% Sesuai Aturan
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 px-6 py-4">
                <button type="button" @click="handleStep({number: '01', label: 'Antrean Verifikasi Pesanan Masuk', active: true, message: 'Pesanan baru yang butuh persetujuan dan penguncian stok sayur.'})"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-left transition-colors bg-brand shadow-sub text-accent">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                    <span class="gpa-meta-lg font-semibold tracking-[0.88px]">01 Antrean Verifikasi Pesanan Masuk</span>
                    <span x-text="'(' + pendingCount() + ' Menunggu)'"></span>
                </button>

                <button type="button" @click="handleStep({number: '02', label: 'Pencetakan Faktur & Tagihan', active: false, message: 'Penerbitan faktur tagihan untuk pesanan yang sudah selesai dikirim.'})"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-left transition-colors bg-surface text-ink-body outline outline-1 outline-line-board hover:bg-surface-muted">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-line-board" aria-hidden="true"></span>
                    <span class="gpa-meta-lg font-semibold tracking-[0.88px]">02 Pencetakan Faktur &amp; Tagihan</span>
                </button>

                <button type="button" @click="handleStep({number: '03', label: 'Pemeriksaan Bukti Transfer', active: false, message: 'Pengecekan bukti pembayaran dari klien reguler.'})"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-left transition-colors bg-surface text-ink-body outline outline-1 outline-line-board hover:bg-surface-muted">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-line-board" aria-hidden="true"></span>
                    <span class="gpa-meta-lg font-semibold tracking-[0.88px]">03 Pemeriksaan Bukti Transfer</span>
                </button>
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
                            Website B2B ({{ $b2bCount ?? 0 }})
                        </button>
                        <button type="button" @click="filter = 'wa'"
                            :class="filterClass('wa')"
                            class="rounded px-2.5 py-1.5 gpa-micro transition-colors">
                            WhatsApp / Manual ({{ $waCount ?? 0 }})
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

                                    @if (! empty($order['chip']))
                                        <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold {{ $order['chip']['class'] }}">
                                            {{ $order['chip']['label'] }}
                                        </span>
                                    @else
                                        <x-gpa.icon name="shield" class="h-5 w-5 shrink-0 text-success-deep" />
                                    @endif
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
                        <span class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink">Skema Pembayaran di Muka (Upfront Payment)</span>
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
                </div>
            </div>

            <div
                class="mt-4 grid gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft sm:grid-cols-2 lg:grid-cols-4">
                <template x-for="cell in selected.pricing" :key="cell.label">
                    <div :class="cell.highlight ? 'rounded-lg bg-surface px-3 py-2 outline outline-1 outline-success-deep/30' : ''">
                        <p class="gpa-micro text-ink-quiet" x-text="cell.label"></p>
                        <p class="mt-1 gpa-meta-lg font-bold tracking-[0.28px]"
                            :class="cell.highlight ? 'text-success-deep' : 'text-ink'">
                            <span x-text="cell.value"></span>
                            <span x-show="cell.suffix" class="gpa-note text-ink-body" x-text="cell.suffix"></span>
                        </p>
                        <p x-show="cell.note" class="mt-0.5 gpa-note text-ink-body" x-text="cell.note"></p>
                    </div>
                </template>
            </div>

            <div class="sticky bottom-4 z-20 mt-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line-soft bg-surface/95 p-4 shadow-xl ring-1 ring-black/5 backdrop-blur-md">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="reject()"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-danger outline outline-1 outline-danger transition-colors hover:bg-danger-soft gpa-meta-lg font-bold tracking-[0.88px]">
                        <x-gpa.icon name="x" class="h-3 w-3 shrink-0" />
                        Tolak Pesanan
                    </button>
                </div>

                <button type="button" @click="verify()"
                    class="inline-flex h-11 items-center gap-2 rounded-lg bg-accent px-6 font-sans text-sm font-extrabold tracking-[0.35px] text-ink shadow-sub transition-colors hover:bg-accent-deep">
                    <x-gpa.icon name="check" class="h-4 w-3 shrink-0" />
                    Setujui Pesanan &amp; Terbitkan Invoice Tagihan
                </button>
            </div>
        </section>
    </div>
@endsection