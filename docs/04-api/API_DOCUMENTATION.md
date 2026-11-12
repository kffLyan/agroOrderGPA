# DOKUMENTASI SPESIFIKASI API & KONTRAK CONTROLLER (S4-05)
## Sistem Rantai Pasok & Tata Kelola Agribisnis B2B — AgroOrder GPA
**Koperasi Produsen Agro Green Pasundan (GPA)**  
**Standar Pelaksanaan Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak 2026/2027**

---

## 1. Ringkasan Arsitektur & Spesifikasi Umum
* **Proyek:** AgroOrder GPA Core Supply Chain & B2B Order Management System
* **Versi Rilis:** `v1.0.0-rc` (Release Candidate)
* **Arsitektur:** Hybrid Web Monolithic MVC (Laravel 11) & Reactive JSON Micro-Endpoints
* **Base URL Lingkungan Pengujian:**
  * **Local Host:** `http://127.0.0.1:8000`
  * **LAN Contingency (Darurat):** `http://{{LAN_IP}}:8000`
  * **Production / Staging Server:** `https://agroorder.site`
* **Format Payload:** `application/x-www-form-urlencoded`, `multipart/form-data`, dan `application/json`
* **Skema Autentikasi:** State-based Session Cookie (`laravel_session`) dengan proteksi ganda `XSRF-TOKEN` / `X-CSRF-TOKEN` dan **Role-Based Access Control (RBAC)**:
  * `KLIEN`: Mitra pemesan sayur grosir B2B (Restoran, Hotel, Supermarket).
  * `SEKRETARIS`: Verifikator pesanan, admin keuangan, penerbit surat jalan dan faktur.
  * `KOORDINATOR`: Manajer gudang dan penerima hasil panen komoditas petani.
  * `ARMADA`: Supir logistik dan kurir serah terima barang (Proof of Delivery / PoD).
  * `DIREKTUR`: Pimpinan eksekutif, audit laporan laba rugi dan piutang.

---

## 2. Standar Kode Respon HTTP (HTTP Status Codes)

| Kode Status | Kategori | Arti & Skenario dalam AgroOrder GPA |
| :---: | :--- | :---- |
| **`200 OK`** | Sukses | Permintaan data berhasil diambil (JSON respon notifikasi, rekap view, ekspor file streaming). |
| **`201 Created`** | Sukses Buat Data | Entitas baru berhasil dibuat (pesanan manual, penerbitan invoice baru). |
| **`302 Found / Redirect`** | Alur MVC | Formulir web berhasil diproses, pengguna diarahkan ke halaman berikutnya dengan flash message sukses. |
| **`400 Bad Request`** | Kesalahan Klien | Alur siklus tidak valid (misal: mencoba menerbitkan surat jalan pada pesanan yang belum diverifikasi). |
| **`401 Unauthorized`** | Belum Login | Sesi pengguna tidak ditemukan atau kadaluwarsa. Sistem mengarahkan pengguna ke `/login`. |
| **`403 Forbidden`** | Akses Ditolak | Pengguna tidak memiliki hak akses role (misal: Klien mencoba mengakses halaman `/sekretaris/verifikasi`). |
| **`404 Not Found`** | Data Tidak Ada | Nomor invoice atau ID pesanan tidak ditemukan di basis data database. |
| **`422 Unprocessable Content`** | Validasi Gagal | Isian formulir melanggar aturan validasi (misal: stok kurang dari kuantitas order, ekstensi file bukti bayar bukan gambar/PDF). |
| **`500 Server Error`** | Kesalahan Server | Kegagalan fatal sistem (ditangani dengan graceful error handler halaman `errors/500`). |

---

## 3. Matriks Endpoint & Kontrak Aksi Controller

### A. Modul Autentikasi & Verifikasi Akun (`Auth`)

#### 1. Login Pengguna (`POST /login`)
* **Controller:** `App\Http\Controllers\Auth\AuthenticatedSessionController@store`
* **Tujuan:** Autentikasi kredensial pengguna ke sistem berbasis peran (RBAC).
* **Headers:** `Content-Type: application/x-www-form-urlencoded`, `X-CSRF-TOKEN`
* **Request Body:**
  ```json
  {
    "email": "klien.sariwangi@agroorder.gpa",
    "password": "password123",
    "remember": "true"
  }
  ```
* **Respon Sukses (`302 Redirect`):** Mengarahkan ke rute `/dashboard` sesuai peran pengguna.
* **Respon Gagal (`422 Unprocessable Content`):**
  ```json
  {
    "message": "Kredensial yang diberikan tidak cocok dengan data kami.",
    "errors": { "email": ["Kredensial yang diberikan tidak cocok dengan data kami."] }
  }
  ```

#### 2. Logout Pengguna (`POST /logout`)
* **Controller:** `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy`
* **Tujuan:** Menghancurkan session token aktif dan mengarahkan pengguna kembali ke halaman landing page publik.
* **Respon Sukses (`302 Redirect`):** Mengarahkan ke rute `/` (beranda publik).

---

### B. Modul Klien B2B (`Klien`)

#### 3. Checkout Pesanan Baru (`POST /pesanan`)
* **Controller:** `App\Http\Controllers\Klien\PesananController@store`
* **Form Request:** `App\Http\Requests\Klien\SimpanPesananRequest`
* **Tujuan:** Mengunci pesanan komoditas sayur, mengkalkulasi subtotal & PPN, serta memesan armada logistik.
* **Request Body (Multipart/Form-Data):**
  | Parameter | Tipe Data | Wajib | Keterangan |
  | :--- | :---: | :---: | :--- |
  | `items[0][product_id]` | integer | Ya | ID komoditas sayuran (merujuk tabel `products`) |
  | `items[0][ordered_qty]` | numeric | Ya | Volume pesanan dalam satuan **Kilogram (Kg)** (min: 5.00) |
  | `target_delivery_date` | date | Ya | Format `YYYY-MM-DD`, minimal H+1 dari hari pemesanan |
  | `delivery_address` | string | Ya | Alamat gudang/dock penerimaan klien |
  | `payment_method` | string | Ya | Pilihan: `TRANSFER_BANK`, `TEMPO_TOP`, `TUNAI` |
  | `payment_proof` | file | Opsional | Berkas bukti transfer (jpg, jpeg, png, pdf, max: 2048 KB) |
* **Respon Sukses (`302 Redirect` ke `/pesanan/{order}`):**
  * `Order` tersimpan dengan nomor faktur unik: `PO/GPA-202611/0012`
  * Status awal: `MENUNGGU_VERIFIKASI`
  * Flash session: `success: "Pesanan berhasil diajukan dan menunggu verifikasi Sekretaris."`

#### 4. Unggah Bukti Pembayaran Faktur (`POST /bukti-pembayaran`)
* **Controller:** `App\Http\Controllers\Klien\BuktiPembayaranController@store`
* **Tujuan:** Mengunggah struk transfer bank atas tagihan pesanan atau invoice berjalan.
* **Request Body (Multipart/Form-Data):**
  | Parameter | Tipe Data | Wajib | Keterangan |
  | :--- | :---: | :---: | :--- |
  | `order_id` | integer | Kondisional | Wajib jika pembayaran per pesanan tunggal |
  | `invoice_id` | integer | Kondisional | Wajib jika pembayaran per faktur gabungan |
  | `payment_method` | string | Ya | Pilihan: `TRANSFER_BANK`, `QRIS`, `GIRO` |
  | `amount` | numeric | Ya | Nominal dana yang ditransfer (Rupiah) |
  | `proof_file` | file | Ya | Foto struk/PDF bukti transfer (max: 2MB) |
* **Respon Sukses (`302 Redirect`):**
  * Rekaman pembayaran baru tersimpan di tabel `payments` dengan status `MENUNGGU_VERIFIKASI`.

#### 5. Sembunyikan Banner Penolakan via AJAX (`POST /notifikasi/dismiss`)
* **Controller:** `App\Http\Controllers\Klien\DashboardController@dismissBanner`
* **Tujuan:** Menyimpan status penutupan banner peringatan merah ke sesi pengguna agar tidak muncul lagi.
* **Request Headers:** `Content-Type: application/json`, `X-CSRF-TOKEN`
* **Request Body (JSON):**
  ```json
  {
    "key": "rejected_order_15"
  }
  ```
* **Respon Sukses (`200 OK` JSON):**
  ```json
  {
    "success": true,
    "message": "Notifikasi berhasil disembunyikan."
  }
  ```

#### 6. Ekspor Rekap Transaksi ke CSV (`GET /pesanan/export`)
* **Controller:** `App\Http\Controllers\Klien\PesananController@exportCsv`
* **Metode Teknis:** `StreamedResponse` terpusat via `streamCsv()` di `Controller.php`.
* **Headers Response:**
  * `Content-Type: text/csv; charset=UTF-8`
  * `Content-Disposition: attachment; filename="Rekap_Pesanan_PO_PT_Sariwangi_20261110.csv"`
* **Fitur:** Dilengkapi **UTF-8 BOM (`\xEF\xBB\xBF`)** dan **`sep=;\r\n`** untuk membuka tabel secara rapi di Microsoft Excel bahasa Indonesia.

---

### C. Modul Sekretariat & Verifikasi (`Sekretaris`)

#### 7. Verifikasi Persetujuan Pesanan (`POST /sekretaris/verifikasi/{order}/setujui`)
* **Controller:** `App\Http\Controllers\Sekretaris\VerifikasiPesananController@approve`
* **Tujuan:** Menyetujui PO klien setelah mengecek ketersediaan komoditas di gudang.
* **Respon Sukses (`302 Redirect`):**
  * Status pesanan beralih: `MENUNGGU_PEMBAYARAN` (atau `LUNAS` jika klien kontrak TEMPO).
  * Kolom `verified_by` mencatat ID staf sekretaris yang bertugas.

#### 8. Verifikasi Penolakan Pesanan (`POST /sekretaris/verifikasi/{order}/tolak`)
* **Controller:** `App\Http\Controllers\Sekretaris\VerifikasiPesananController@reject`
* **Request Body:**
  ```json
  {
    "rejection_reason": "Kapasitas panen tomat tidak memenuhi standar mutu grading A."
  }
  ```
* **Respon Sukses (`302 Redirect`):**
  * Status pesanan beralih ke `BATAL`.
  * Sistem memicu notifikasi penolakan pesanan di dashboard klien.

#### 9. Penerbitan Surat Jalan & Penugasan Armada (`POST /sekretaris/surat-jalan/{order}/tugaskan-supir`)
* **Controller:** `App\Http\Controllers\Sekretaris\SuratJalanController@assignDriver`
* **Request Body:**
  ```json
  {
    "driver_id": 4,
    "vehicle_plate_number": "D 8122 GPA"
  }
  ```
* **Respon Sukses (`302 Redirect`):**
  * Nomor surat jalan diterbitkan otomatis: `SJ/GPA-202611/0045`
  * Status pesanan beralih ke: `SIAP_KIRIM`.

---

### D. Modul Armada & Logistik Lapangan (`Armada`)

#### 10. Konfirmasi Keberangkatan Truk (`POST /armada/surat-jalan/{order}/mulai`)
* **Controller:** `App\Http\Controllers\Armada\SuratJalanArmadaController@startRoute`
* **Tujuan:** Supir mengonfirmasi bahwa truk telah memuat sayuran dan berangkat dari gudang.
* **Respon Sukses (`302 Redirect`):**
  * Status pesanan beralih ke: `DALAM_PENGIRIMAN`.
  * Kolom `departure_time` terisi waktu saat tombol ditekan (`now()`).
  * Widget *Live Tracking* di dashboard klien aktif menampilkan nama driver dan plat nomor.

#### 11. Finalisasi Serah Terima Barang / PoD (`POST /armada/bukti-terima/{order}/selesai`)
* **Controller:** `App\Http\Controllers\Armada\BuktiTerimaController@store`
* **Request Body (Multipart/Form-Data):**
  | Parameter | Tipe Data | Wajib | Keterangan |
  | :--- | :---: | :---: | :--- |
  | `manifest_photo` | file | Ya | Foto timbangan tera netto di kebun/gudang (max: 4MB) |
  | `dock_photo` | file | Ya | Foto serah terima di dermaga/dock klien (max: 4MB) |
  | `recipient_name` | string | Ya | Nama penanggung jawab penerima di lokasi klien |
  | `received_notes` | string | Opsional | Catatan jika ada susut timbangan atau retur sayur |
* **Respon Sukses (`302 Redirect`):**
  * Status pesanan beralih menjadi: `SELESAI` (atau `SELESAI_CATATAN` jika terdapat retur).
  * Kolom `arrival_time` otomatis terkunci waktu serah terima.

---

## 4. Panduan Eksekusi Pengujian (Testing Manual & Otomatis)

### Pengujian Otomatis (PHPUnit Feature Tests)
Seluruh endpoint di atas telah divalidasi dengan rangkaian uji otomatis bawaan Laravel:
```powershell
# Menjalankan seluruh pengujian endpoint pesanan & otorisasi
php artisan test --filter=Pesanan

# Menjalankan seluruh pengujian fungsional backend (215 tests, 865 assertions)
php artisan test
```

### Pengujian Interaktif (Postman Collection)
1. Buka aplikasi **Postman**.
2. Klik tombol **Import** pada pojok kiri atas.
3. Arahkan ke berkas: `docs/04-api/agroorder_api_collection.json`.
4. Jalankan permintaan secara berurutan sesuai skenario siklus B2B di lingkungan lokal atau staging.

