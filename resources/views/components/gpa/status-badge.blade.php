@props([
    'status' => null,
    'label' => null,
    'tone' => null,
    'dot' => false,
    'class' => '',
])

@php
    $statusKey = $status instanceof \BackedEnum ? $status->value : (string) ($status ?? '');

    // Resolusi label teks resmi
    $displayLabel = $label ?? ($status instanceof \BackedEnum && method_exists($status, 'label') ? $status->label() : match ($statusKey) {
        'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
        'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
        'LUNAS', 'VERIFIED', 'SUCCESS' => 'Lunas Sah',
        'SIAP_KIRIM' => 'Siap Dikirim',
        'DALAM_PENGIRIMAN' => 'Dalam Pengiriman',
        'SELESAI' => 'Selesai Sempurna',
        'SELESAI_CATATAN' => 'Selesai Catatan',
        'DITOLAK', 'FAILED', 'REJECTED' => 'Ditolak',
        'BATAL' => 'Dibatalkan',
        'ACTIVE' => 'Aktif',
        'PENDING' => 'Menunggu',
        default => strtoupper(str_replace('_', ' ', $statusKey)),
    });

    // Resolusi tone warna UI
    $displayTone = $tone ?? ($status instanceof \BackedEnum && method_exists($status, 'tone') ? $status->tone() : match ($statusKey) {
        'LUNAS', 'VERIFIED', 'SUCCESS', 'SELESAI', 'ACTIVE' => 'success',
        'DITOLAK', 'FAILED', 'REJECTED', 'BATAL' => 'danger',
        'MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'PENDING' => 'warning',
        'SIAP_KIRIM', 'DALAM_PENGIRIMAN' => 'info',
        default => 'neutral',
    });

    $tones = [
        'success' => 'bg-accent text-ink ring-accent-edge',
        'danger'  => 'bg-danger-soft text-danger ring-danger/40',
        'warning' => 'bg-warning/10 text-warning-deep ring-warning/40',
        'info'    => 'bg-blue-50 text-blue-800 ring-blue-300',
        'neutral' => 'bg-surface-track text-ink-body ring-line-board',
    ];

    $badgeStyle = $tones[$displayTone] ?? $tones['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider ring-1 ring-inset ' . $badgeStyle . ' ' . $class]) }}>
    @if ($displayTone === 'success')
        <x-gpa.icon name="check" class="h-2.5 w-2.5" />
    @elseif ($displayTone === 'danger')
        <x-gpa.icon name="x" class="h-2.5 w-2.5" />
    @elseif ($displayTone === 'warning')
        <span class="h-1.5 w-1.5 rounded-full bg-warning-deep" aria-hidden="true"></span>
    @elseif ($dot)
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70" aria-hidden="true"></span>
    @endif
    <span>{{ $displayLabel }}</span>
</span>
