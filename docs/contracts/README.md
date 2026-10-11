# Kontrak Data AgroOrder GPA

Folder ini adalah **kontrak data** antara frontend dan backend.

Backend **wajib** mengembalikan data dengan bentuk yang sama dengan `example` pada
setiap file kontrak. Frontend **tidak boleh** membaca key yang tidak ada di kontrak.

## Aturan main

1. **Satu objek = satu file.** Nama file memakai nama objek bisnis dalam Kiblat §23.
2. **Kontrak hanya berisi data domain.** Tidak ada `label`, `title`, `subtitle`,
   `badge`, `icon`, `tone`, `class`. Nada visual dan copy Bahasa adalah urusan
   frontend (`app/Support/*Data.php`).
3. **`example` harus realistis** dan memakai angka yang sudah dipakai pada mockup
   aplikasi, supaya frontend bisa memverifikasi tampilannya tanpa guessing.
4. **Versi.** Kolom `version` pada file kontrak naik setiap ada perubahan yang
   tidak backward-compatible. Hapus versi lama setelah semua layarMigrasi.
5. **Menambah field baru** bersifat backward-compatible dan tidak menaikkan versi.
6. **Menghapus / mengubah tipe field** menaikkan versi dan wajib dikomunikasikan
   sebelum dikerjakan frontend.
7. **Angka disimpan sebagai angka.** Currency rupiah tanpa pemisah ribuan,
   berat dalam kilogram dengan titik desimal (`795.0`), waktu dalam ISO-8601
   (`2026-10-24T05:42:00+07:00`).

## Format file

```json
{
  "object": "Product",
  "version": "1.0",
  "table": "products",
  "description": "...",
  "fields": [
    {
      "name": "sku",
      "type": "string",
      "required": true,
      "nullable": false,
      "note": "Kode internal komoditas, contoh: VEG-ROM-01"
    }
  ],
  "enums": { "status": ["tersedia", "terbatas", "habis"] },
  "relations": { "inventory": "Inventory[]" },
  "example": { "id": 1, "sku": "VEG-ROM-01" }
}
```

Tipe yang diizinkan: `string`, `text`, `int`, `decimal`, `bool`, `datetime`,
`date`, `enum`, `json`.

## Verifikasi

```bash
php artisan test --filter=ContractShapeTest
```

Test memverifikasi setiap file: nama objek unik, nama field unik dan snake_case,
tipe dikenal, dan `example` cocok dengan `fields` (key wajib ada, tipe cocok,
nilai enum valid, tidak ada key contoh yang tidak ada di `fields`).

## Objek yang sudah dikontrakkan

| Objek | File | Src |
|---|---|---|
| User | [user.json](user.json) | §6.1, §7.1, §23 |
| Product | [product.json](product.json) | §7.2, §23 |
| Inventory | [inventory.json](inventory.json) | §7.3, §8.2, §23 |
| Order | [order.json](order.json) | §7.4, §5.3, §23 |
| Order Item | [order-item.json](order-item.json) | §23 |
| Actual Weight | [actual-weight.json](actual-weight.json) | §9, Rule 05 |
| Delivery | [delivery.json](delivery.json) | §11, §23 |
| Delivery Item | [delivery-item.json](delivery-item.json) | §23 |
| Surat Jalan | [surat-jalan.json](surat-jalan.json) | §10, Rule 05 |
| Proof of Delivery | [pod.json](pod.json) | §12, Rule 06 |
| Return | [return.json](return.json) | §13 |
| Invoice | [invoice.json](invoice.json) | §14, §23 |
| Invoice Item | [invoice-item.json](invoice-item.json) | §23 |
| Payment | [payment.json](payment.json) | §15.4 |

## Objek yang belum dikontrakkan

Sudah ada di Kiblat §23 tetapi kontrak Holder-nya menyusul, agar tidak
bertentangan dengan Phase 7:

- `Role` — menyusul bersama implementasi RBAC (Rule 08).
- `Client` — digabung ke `user.json` selama tabel `users` masih memuat
  `client_type` dan field formulir pendaftaran.
- `Supplier / Farmer` — menyusul saat modul buffer stock (§7.3) dikerjakan.
- `Contract` dan `Contract Approval` — menyusul saat layar Kontrak / Approval
  (§22 Sekre) dikerjakan.
- `Compensation` — menyusul saat alur retur → kompensasi kuota (§13) dikerjakan.