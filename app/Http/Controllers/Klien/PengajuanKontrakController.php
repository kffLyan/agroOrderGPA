<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Requests\Klien\SimpanPengajuanKontrakRequest;
use App\Models\Contract;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

// Controller pengajuan proposal kontrak kemitraan B2B untuk Klien
class PengajuanKontrakController extends Controller
{
    // Mengajukan proposal kemitraan kontrak suplai rutin B2B dan tempo pembayaran ke Direktur Utama
    public function store(SimpanPengajuanKontrakRequest $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        // Memeriksa apakah klien masih memiliki proposal kontrak yang berstatus menunggu peninjauan
        $existingPending = Contract::where('user_id', $user->id)
            ->whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->first();

        if ($existingPending) {
            return back()->withErrors([
                'error' => "Anda telah memiliki pengajuan kontrak kemitraan ({$existingPending->contract_number}) yang sedang dalam peninjauan Direktur Utama GPA.",
            ]);
        }

        $validated = $request->validated();

        // Memperbarui profil legalitas usaha dan narahubung PIC klien ke database
        $userUpdates = array_filter([
            'company_name' => $validated['company_name'] ?? null,
            'address'      => $validated['address'] ?? null,
            'pic_name'     => $validated['pic_name'] ?? null,
            'pic_phone'    => $validated['pic_phone'] ?? null,
        ]);
        if (!empty($userUpdates)) {
            $user->update($userUpdates);
        }

        $product = Product::findOrFail($validated['product_id']);
        $contractNumber = 'PKS-REQ-' . date('Ym') . '-' . str_pad((string) (Contract::count() + 1), 4, '0', STR_PAD_LEFT);

        $proposedPrice = !empty($validated['proposed_price_per_kg'])
            ? (float) $validated['proposed_price_per_kg']
            : (float) $product->base_price;

        $contract = Contract::create([
            'user_id'                    => $user->id,
            'product_id'                 => $product->id,
            'contract_number'            => $contractNumber,
            'fixed_price_per_kg'         => $proposedPrice,
            'top_days'                   => (int) $validated['top_days'],
            'committed_volume_per_cycle' => (float) $validated['committed_volume_per_cycle'],
            'status'                     => 'PENDING_APPROVAL',
        ]);

        // Hapus riwayat penolakan lama saat klien berhasil mengajukan proposal baru
        try {
            $tataKelolaService = app(\App\Services\TataKelolaService::class);
            $settings = $tataKelolaService->getSettings();
            if (isset($settings['rejected_contracts'][$user->id])) {
                unset($settings['rejected_contracts'][$user->id]);
                $tataKelolaService->updateSettings($settings);
            }
        } catch (\Throwable $e) {
            // Abaikan jika service tidak dapat diakses
        }

        $priceFormatted = 'Rp ' . number_format($proposedPrice, 0, ',', '.');
        $priceDiff = $proposedPrice - (float) $product->base_price;
        $priceSummary = '';
        if ($priceDiff < 0) {
            $discountPct = round((abs($priceDiff) / (float) $product->base_price) * 100);
            $priceSummary = " (Usulan Harga: {$priceFormatted}/kg, diskon {$discountPct}%)";
        } elseif ($priceDiff > 0) {
            $priceSummary = " (Usulan Harga: {$priceFormatted}/kg)";
        } else {
            $priceSummary = " (Harga Katalog: {$priceFormatted}/kg)";
        }

        $picPhone = $user->fresh()->pic_phone ?: $user->phone;
        $whatsAppNote = $picPhone ? " Tim Kemitraan GPA akan segera menghubungi WhatsApp PIC ({$picPhone}) untuk negosiasi draf PKS dan finalisasi harga." : "";

        return redirect()->route('klien.dashboard')->with(
            'success',
            "Pengajuan Kontrak Kemitraan {$contract->contract_number} ({$product->name}, {$contract->committed_volume_per_cycle} kg/siklus{$priceSummary}, TOP {$contract->top_days}D) berhasil dikirimkan ke Direktur Utama GPA.{$whatsAppNote}"
        );
    }
}
