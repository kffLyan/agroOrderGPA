<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Inspeksi Pengawasan Pesanan: {{ $order->order_number }}
    </x-slot>

    <div class="mx-auto flex max-w-shell flex-col gap-6 text-xs">
        {{-- Bar Aksi & Status Pesanan --}}
        <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-surface p-4 sm:p-5 shadow-card outline outline-1 outline-line-board/50">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('direktur.dashboard') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-surface-shell px-3 py-2 text-xs font-semibold text-ink transition-colors hover:bg-surface-track">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                    <span>Kembali ke Dasbor</span>
                </a>

                <div class="h-4 w-px bg-line-board hidden sm:block"></div>

                <div class="flex items-center gap-2">
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-quiet">No. PO:</span>
                    <span class="font-mono text-sm font-bold text-ink">{{ $order->order_number }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @php
                    $badgeClasses = match($order->status) {
                        'SELESAI' => 'bg-accent text-success-deep outline-success-deep',
                        'SELESAI_CATATAN' => 'bg-accent text-ink outline-line-board',
                        'DALAM_PENGIRIMAN', 'SIAP_KIRIM' => 'bg-surface-shell text-ink outline-line-board',
                        'LUNAS' => 'bg-accent text-success-deep outline-success-deep',
                        'MENUNGGU_PEMBAYARAN' => 'bg-surface-track text-ink outline-line-board',
                        'MENUNGGU_VERIFIKASI' => 'bg-warning-soft text-warning-deep outline-warning-caution',
                        'BATAL' => 'bg-danger-soft/40 text-danger outline-danger',
                        default => 'bg-surface-shell text-ink-quiet outline-line-board',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold outline outline-1 {{ $badgeClasses }}">
                    Status: {{ str_replace('_', ' ', $order->status) }}
                </span>

                <div class="flex items-center gap-1.5 pl-2 border-l border-line-board">
                    <a href="{{ route('prints.surat-jalan', ['order_id' => $order->id]) }}" target="_blank"
                        title="Cetak Surat Jalan Resmi (A5)"
                        class="inline-flex items-center gap-1 rounded-lg bg-surface-shell px-2.5 py-1.5 font-mono text-[11px] font-bold text-ink outline outline-1 outline-line-board hover:bg-surface-track hover:text-success-deep transition-colors shadow-xs">
                        <x-gpa.icon name="truck" class="h-3.5 w-3.5 text-success-deep" />
                        <span>Surat Jalan</span>
                    </a>

                    <a href="{{ route('prints.invoice', ['invoice' => $order->invoice?->invoice_number ?? $order->order_number]) }}" target="_blank"
                        title="Cetak Faktur Penagihan Resmi"
                        class="inline-flex items-center gap-1 rounded-lg bg-accent/40 px-2.5 py-1.5 font-mono text-[11px] font-bold text-success-deep outline outline-1 outline-accent hover:bg-accent transition-colors shadow-xs">
                        <x-gpa.icon name="invoice" class="h-3.5 w-3.5 text-success-deep" />
                        <span>Faktur</span>
                    </a>

                    <a href="{{ route('prints.thermal-receipt', ['invoice' => $order->invoice?->invoice_number ?? $order->order_number]) }}" target="_blank"
                        title="Cetak Struk Kasir Thermal"
                        class="inline-flex items-center gap-1 rounded-lg bg-surface-shell px-2.5 py-1.5 font-mono text-[11px] font-bold text-ink outline outline-1 outline-line-board hover:bg-surface-track hover:text-brand transition-colors shadow-xs">
                        <x-gpa.icon name="receipt" class="h-3.5 w-3.5 text-brand" />
                        <span>Struk</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Grid 4 Pilar Pengawasan Pesanan --}}
        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <!-- 1. Klien Pemesan -->
            <div class="rounded-2xl bg-surface p-4 shadow-card outline outline-1 outline-line-board/40 flex flex-col justify-between space-y-3">
                <div>
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-quiet block">1. ENTITAS PEMBELI</span>
                    <div class="font-sans font-bold text-ink text-sm sm:text-base mt-1">
                        {{ $order->user->company_name ?? $order->user->name }}
                    </div>
                    <div class="text-ink-body mt-2 space-y-0.5 leading-relaxed">
                        <div>Akun: <strong class="text-ink">{{ $order->user->name }}</strong></div>
                        <div>Kontak: <span class="font-mono text-ink">{{ $order->user->phone }}</span></div>
                        <div class="flex items-center gap-1.5 pt-0.5">
                            <span>Tipe Klien:</span>
                            @if(($order->user->client_type ?? 'REGULER') === 'B2B_KONTRAK')
                                <span class="rounded bg-accent/40 px-2 py-0.5 text-[10px] font-bold text-success-deep outline outline-1 outline-success-deep font-mono">MITRA KONTRAK B2B</span>
                            @else
                                <span class="rounded bg-surface-track px-2 py-0.5 text-[10px] font-bold text-ink outline outline-1 outline-line-board font-mono">KLIEN REGULER</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pt-2 text-[11px] text-ink-body border-t border-line-soft">
                    <strong class="text-ink">Tujuan:</strong> {{ $order->delivery_address }}
                </div>
            </div>

            <!-- 2. Verifikasi Sekretaris -->
            <div class="rounded-2xl bg-surface p-4 shadow-card outline outline-1 outline-line-board/40 flex flex-col justify-between space-y-3">
                <div>
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-quiet block">2. TAHAP ADMINISTRASI</span>
                    <div class="font-sans font-bold text-ink text-sm sm:text-base mt-1 flex items-center gap-2">
                        @if ($order->verifier)
                            <span class="h-2 w-2 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <span>Terverifikasi Sah</span>
                        @else
                            <span class="h-2 w-2 rounded-full bg-warning-caution" aria-hidden="true"></span>
                            <span>Menunggu Verifikasi</span>
                        @endif
                    </div>
                    <div class="text-ink-body mt-2 space-y-0.5 leading-relaxed">
                        <div>Petugas: <strong class="text-ink">{{ $order->verifier->name ?? 'Staf Sekretariat' }}</strong></div>
                        <div>Waktu Pesan: <span class="font-mono">{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
                        <div>Target Kirim: <strong class="text-success-deep font-mono">{{ $order->target_delivery_date ? $order->target_delivery_date->format('d F Y') : '-' }}</strong></div>
                    </div>
                </div>
                <div class="pt-2 text-[11px] text-ink-body border-t border-line-soft">
                    Jalur Order: <span class="font-semibold text-ink">{{ $order->order_source }}</span>
                </div>
            </div>

            <!-- 3. Eksekusi Armada & Logistik -->
            <div class="rounded-2xl bg-surface p-4 shadow-card outline outline-1 outline-line-board/40 flex flex-col justify-between space-y-3">
                <div>
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-quiet block">3. DISTRIBUSI &amp; ARMADA</span>
                    <div class="font-sans font-bold text-ink text-sm sm:text-base mt-1">
                        {{ $order->driver ? $order->driver->name : 'Belum Ditugaskan' }}
                    </div>
                    <div class="text-ink-body mt-2 space-y-0.5 leading-relaxed">
                        <div>Plat Armada: <strong class="font-mono text-ink">{{ $order->vehicle_plate_number ?? '-' }}</strong></div>
                        <div>Surat Jalan: <strong class="font-mono text-ink">{{ $order->surat_jalan_number ?? '-' }}</strong></div>
                        <div>Penerima: <strong class="text-ink">{{ $order->received_by_name ?? '-' }}</strong></div>
                    </div>
                </div>
                <div class="pt-2 text-[11px] text-ink-body border-t border-line-soft">
                    Waktu Sampai: <span class="font-mono">{{ $order->arrival_time ? $order->arrival_time->format('d/m/Y H:i') : '-' }}</span>
                </div>
            </div>

            <!-- 4. Faktur & Finansial -->
            <div class="rounded-2xl bg-surface p-4 shadow-card outline outline-1 outline-line-board/40 flex flex-col justify-between space-y-3">
                <div>
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-quiet block">4. STATUS FAKTUR &amp; PEMBAYARAN</span>
                    <div class="font-sans font-bold text-success-deep text-sm sm:text-base mt-1 font-mono">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </div>
                    <div class="text-ink-body mt-2 space-y-0.5 leading-relaxed">
                        <div>No. Faktur: <span class="font-mono font-bold text-ink">{{ $order->invoice->invoice_number ?? 'Belum terbit' }}</span></div>
                        <div>Status Faktur: <strong class="text-brand">{{ $order->invoice->status ?? 'UNPAID' }}</strong></div>
                        <div>Jatuh Tempo: <span class="font-mono">{{ $order->invoice && $order->invoice->due_date ? date('d/m/Y', strtotime($order->invoice->due_date)) : '-' }}</span></div>
                    </div>
                </div>
                <div class="pt-2 text-[11px] text-ink-body border-t border-line-soft">
                    Pembayaran: <span class="font-semibold text-ink">{{ $order->payments->first() ? $order->payments->first()->status : 'Belum tercatat' }}</span>
                </div>
            </div>
        </section>

        {{-- Tabel Rincian Komoditas & Timbangan Lapangan --}}
        <section class="rounded-2xl bg-surface shadow-card outline outline-1 outline-line-board/50 overflow-hidden">
            <header class="p-4 sm:p-5 border-b border-line-soft bg-surface-shell/40 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-sans font-bold text-sm sm:text-base text-ink flex items-center gap-2">
                        <x-gpa.icon name="package" class="h-4 w-4 text-success-deep" />
                        Rincian Komoditas &amp; Nilai Transaksi
                    </h3>
                    <p class="text-xs text-ink-body mt-0.5">Harga dan kuantitas disepakati secara pasti pada pemesanan</p>
                </div>
                <span class="gpa-micro-bold uppercase tracking-wider rounded-lg bg-surface-shell px-2.5 py-1 text-ink outline outline-1 outline-line-board">
                    {{ count($order->orderItems) }} Item Komoditas
                </span>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-brand text-accent">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px]">Komoditas Sayuran</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px]">Grade</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-right">Qty Pesan</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-right">Harga Satuan</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-right">Subtotal</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px]">Catatan / Retur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        @foreach($order->orderItems as $item)
                            <tr class="hover:bg-surface-shell/60 transition-colors">
                                <td class="px-4 py-3 font-semibold text-ink">
                                    <div class="font-sans font-bold text-sm">{{ $item->product->name }}</div>
                                    <span class="block text-[10px] text-ink-quiet font-mono mt-0.5">SKU: {{ $item->product->sku }}</span>
                                </td>
                                <td class="px-4 py-3 text-ink-body font-mono">
                                    {{ $item->product->grade ?? 'Grade A' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-ink">
                                    {{ number_format($item->ordered_qty, 1) }} kg
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-ink-body">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}/kg
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-brand text-sm">
                                    Rp {{ number_format($item->subtotal ?: ($item->unit_price * $item->ordered_qty), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-[11px]">
                                    @if($item->returned_weight > 0)
                                        <span class="inline-flex items-center gap-1 rounded bg-danger-soft/40 px-2 py-0.5 font-semibold text-danger">
                                            Retur: {{ number_format($item->returned_weight, 1) }} kg ({{ $item->return_reason ?? 'Afkir' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-success-deep font-semibold">
                                            <x-gpa.icon name="check" class="h-3 w-3" />
                                            Sesuai standar
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-brand bg-surface-shell">
                        <tr class="font-bold text-ink">
                            <td colspan="4" class="px-4 py-3.5 text-right gpa-micro-bold uppercase tracking-[1.08px]">
                                TOTAL NILAI TRANSAKSI PESANAN:
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-base font-extrabold text-brand">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- Bukti Pengantaran / Surat Jalan / Pembayaran --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bukti Pengantaran (PoD) -->
            <div class="rounded-2xl bg-surface p-5 shadow-card outline outline-1 outline-line-board/50 space-y-4">
                <div class="flex items-center justify-between border-b border-line-soft pb-3">
                    <h4 class="font-sans font-bold text-ink text-sm uppercase tracking-wider flex items-center gap-2">
                        <x-gpa.icon name="truck" class="h-4 w-4 text-success-deep" />
                        Bukti Penerimaan Barang (Proof of Delivery)
                    </h4>
                    @if($order->pod_photo_url)
                        <span class="rounded bg-accent px-2 py-0.5 text-[10px] font-bold text-success-deep uppercase">Terunggah</span>
                    @endif
                </div>

                @php
                    $podUrls = $order->pod_photo_asset_urls ?? [];
                @endphp

                @if(!empty($podUrls))
                    <div class="p-3 bg-surface-shell rounded-xl border border-line-board/40 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-ink-body">Penerima Barang di Lokasi:</span>
                            <span class="font-bold text-success-deep">{{ $order->received_by_name ?? 'Staf Receiving Klien' }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-line-board/30">
                            @foreach ($podUrls as $index => $photoUrl)
                                <a href="{{ $photoUrl }}" target="_blank"
                                    class="group relative flex flex-col items-center justify-center overflow-hidden rounded-lg border border-line-board bg-white transition hover:ring-2 hover:ring-brand">
                                    <img src="{{ $photoUrl }}" alt="Foto PoD {{ $index + 1 }}" class="h-32 w-full object-cover transition-transform group-hover:scale-105" />
                                    <span class="absolute bottom-0 inset-x-0 bg-black/60 py-1 text-center font-mono text-[10px] font-bold text-white">
                                        Foto {{ $index + 1 }} (Klik Perbesar)
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-8 bg-surface-shell/50 rounded-xl text-center text-ink-quiet border border-dashed border-line-board">
                        <x-gpa.icon name="camera" class="mx-auto h-6 w-6 text-ink-quiet/50 mb-1" />
                        Foto Surat Jalan bertanda tangan belum diunggah supir.
                    </div>
                @endif
            </div>

            <!-- Bukti Pembayaran -->
            <div class="rounded-2xl bg-surface p-5 shadow-card outline outline-1 outline-line-board/50 space-y-4">
                <div class="flex items-center justify-between border-b border-line-soft pb-3">
                    <h4 class="font-sans font-bold text-ink text-sm uppercase tracking-wider flex items-center gap-2">
                        <x-gpa.icon name="banknote" class="h-4 w-4 text-success-deep" />
                        Rekam Jejak Pelunasan &amp; Mutasi Bank
                    </h4>
                    <span class="rounded bg-surface-shell px-2 py-0.5 text-[10px] font-bold text-ink-body uppercase">{{ count($order->payments) }} Entri</span>
                </div>

                @forelse($order->payments as $payment)
                    @php
                        $slipUrl = $payment->proof_url
                            ? (str_starts_with($payment->proof_url, 'http') ? $payment->proof_url : asset('storage/' . $payment->proof_url))
                            : null;
                    @endphp
                    <div class="p-3.5 bg-surface-shell rounded-xl border border-line-board/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-ink">{{ $payment->payment_reference }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $payment->status === 'LUNAS' || $payment->status === 'PAID' ? 'bg-accent text-success-deep' : 'bg-surface-track text-ink' }}">
                                {{ $payment->status }}
                            </span>
                        </div>
                        <div class="text-sm font-bold text-brand font-mono">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }} via {{ $payment->payment_method }}
                        </div>
                        <div class="text-[11px] text-ink-body">
                            Diverifikasi: <strong class="text-ink">{{ $payment->verifier->name ?? 'Sekretaris' }}</strong> pada {{ $payment->paid_at ? date('d/m/Y H:i', strtotime($payment->paid_at)) : '-' }}
                        </div>

                        @if($slipUrl)
                            <div class="pt-2 border-t border-line-board/30 flex items-center justify-between">
                                <span class="text-[10px] font-mono text-ink-quiet">Slip Bukti Transfer:</span>
                                <a href="{{ $slipUrl }}" target="_blank"
                                    class="inline-flex items-center gap-1 rounded bg-accent px-2 py-1 font-mono text-[10px] font-bold text-success-deep outline outline-1 outline-success-deep hover:bg-accent-deep transition-colors">
                                    <x-gpa.icon name="external-link" class="h-3 w-3" />
                                    <span>Lihat Berkas Slip</span>
                                </a>
                            </div>
                        @endif

                        @if($payment->verification_note)
                            <div class="text-[11px] text-ink-body bg-white/70 p-2 rounded border border-line-soft italic mt-1">
                                Catatan: {{ $payment->verification_note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 bg-surface-shell/50 rounded-xl text-center text-ink-quiet border border-dashed border-line-board">
                        <x-gpa.icon name="receipt" class="mx-auto h-6 w-6 text-ink-quiet/50 mb-1" />
                        Belum ada data pembayaran untuk pesanan ini.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-dynamic-component>
