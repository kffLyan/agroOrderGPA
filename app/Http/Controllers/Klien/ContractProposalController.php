<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractProposalController extends Controller
{
    /**
     * Mengajukan proposal kemitraan B2B Kontrak oleh Klien Reguler
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        // Cek apakah ada pengajuan yang masih pending
        $existingPending = Contract::where('user_id', $user->id)
            ->whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->first();

        if ($existingPending) {
            return back()->withErrors([
                'error' => "Anda telah memiliki pengajuan kontrak kemitraan ({$existingPending->contract_number}) yang sedang dalam peninjauan Direktur Utama GPA.",
            ]);
        }

        $validated = $request->validate([
            'product_id'                 => ['required', 'exists:products,id'],
            'committed_volume_per_cycle' => ['required', 'numeric', 'min:50', 'max:50000'],
            'top_days'                   => ['required', 'integer', 'in:14,30,45'],
            'company_name'               => ['nullable', 'string', 'max:150'],
            'address'                    => ['nullable', 'string', 'max:255'],
            'pic_name'                   => ['nullable', 'string', 'max:100'],
            'pic_phone'                  => ['nullable', 'string', 'max:25'],
        ], [
            'product_id.required'                 => 'Pilih komoditas utama yang ingin dikontrakkan.',
            'product_id.exists'                   => 'Komoditas yang dipilih tidak valid.',
            'committed_volume_per_cycle.required' => 'Komitmen volume per siklus wajib diisi.',
            'committed_volume_per_cycle.min'      => 'Minimal komitmen volume adalah 50 kg per siklus pengiriman.',
            'top_days.required'                   => 'Pilih termin pembayaran (TOP) yang diajukan.',
            'top_days.in'                         => 'Pilihan TOP harus 14, 30, atau 45 hari.',
        ]);

        // Perbarui data identitas usaha dan kontak PIC pada kolom tabel users yang sudah ada
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
        $contractNumber = 'PKS-REQ-' . date('Ym') . '-' . str_pad(Contract::count() + 1, 4, '0', STR_PAD_LEFT);

        $contract = Contract::create([
            'user_id'                    => $user->id,
            'product_id'                 => $product->id,
            'contract_number'            => $contractNumber,
            'fixed_price_per_kg'         => $product->base_price,
            'top_days'                   => (int) $validated['top_days'],
            'committed_volume_per_cycle' => (float) $validated['committed_volume_per_cycle'],
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $picPhone = $user->fresh()->pic_phone ?: $user->phone;
        $waNote = $picPhone ? " Tim Kemitraan GPA akan segera menghubungi WhatsApp PIC ({$picPhone}) untuk due diligence dan koordinasi draf PKS." : "";

        return redirect()->route('klien.dashboard')->with(
            'success',
            "Pengajuan Kontrak Kemitraan {$contract->contract_number} ({$product->name}, {$contract->committed_volume_per_cycle} kg, TOP {$contract->top_days}D) berhasil dikirimkan ke Direktur Utama GPA.{$waNote}"
        );
    }
}
