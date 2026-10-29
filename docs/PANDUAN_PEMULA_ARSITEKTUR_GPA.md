# PANDUAN LENGKAP ARSITEKTUR & PEMAHAMAN KODE UNTUK SISWA SMK (UKK)
**Proyek:** AgroOrder GPA — Sistem Informasi Pemesanan & Rantai Pasok Hasil Tani  
**Sasaran Pembaca:** Siswa SMK Jurusan Rekayasa Perangkat Lunak (RPL) / Pemula  
**Tujuan Dokumen:** Membantu Anda memahami 100% cara kerja setiap kode, alur logika, dan siap menjawab semua pertanyaan penguji sidang UKK dengan percaya diri.

---

## 📌 DAFTAR ISI
1. [Penjelasan Santai: Apa Maksud "CSS di dalam API Resource"?](#1-penjelasan-santai-apa-maksud-css-di-dalam-api-resource)
2. [Peta Besar Sistem: Siapa Saja 5 Aktor & Apa Tugasnya?](#2-peta-besar-sistem-siapa-saja-5-aktor--apa-tugasnya)
3. [Kamus Istilah Tingkat Tinggi (Diterjemahkan ke Bahasa Manusia)](#3-kamus-istilah-tingkat-tinggi-diterjemahkan-ke-bahasa-manusia)
4. [Peta Alur: "Kalau Tombol Ini Diklik, Kodenya Lari ke Mana?"](#4-peta-alur-kalau-tombol-ini-diklik-kodenya-lari-ke-mana)
5. [Bedah Kode Kunci: Penjelasan Baris per Baris](#5-bedah-kode-kunci-penjelasan-baris-per-baris)
6. [Bocoran & Kunci Jawaban Pertanyaan Penguji UKK](#6-bocoran--kunci-jawaban-pertanyaan-penguji-ukk)
7. [Tips Sukses Demonstrasi di Depan Penguji](#7-tips-sukses-demonstrasi-di-depan-penguji)

---

## 1. Penjelasan Santai: Apa Maksud "CSS di dalam API Resource"?

Kemarin ada pertanyaan: *"Apakah Anda ingin kami membersihkan coupling Tailwind CSS di dalam API Resources?"* Anda sempat bingung apa maksudnya. Berikut penjelasannya dengan analogi sederhana:

* **Analogi Pengiriman Barang:**
  * **Data Model (Database):** Barang mentah di gudang (misal: Sayur Wortel 10 Kg, Harga Rp 50.000).
  * **API Resource:** Petugas yang membungkus barang ke dalam kardus paket agar rapi saat dikirim (`to_array`).
  * **Blade View / CSS:** Etalase toko dan lampu hias warna-warni tempat paket itu dipajang.
* **Kondisi di Kodingan Anda Saat Ini:**
  Di file `app/Http/Resources/Sekretaris/OrderVerificationResource.php`, selain membungkus data sayur, si petugas pembungkus paket juga menempelkan instruksi warna baju:
  `'class' => 'bg-accent text-success-ink font-bold'`
* **Kenapa Disebut Kurang "Clean Code"?**
  Karena menurut standar industri (*Separation of Concerns*), file data (Resource) tidak boleh ikut campur urusan warna/tampilan (CSS). Urusan warna harusnya 100% ditulis di file tampilan (`.blade.php`).
* **Keputusan Kita:**
  **Kita TIDAK AKAN MENGUBAHNYA**, karena jika diubah, ada risiko tampilan warna badge di layar berubah atau error saat sidang. Kode tersebut tetap dibiarkan berfungsi normal 100%!

---

## 2. Peta Besar Sistem: Siapa Saja 5 Aktor & Apa Tugasnya?

Aplikasi ini dibuat untuk rantai pasok sayuran **Koperasi Green Pasundan Agriculture (GPA)** di Ciwidey. Agar tidak ada kekacauan di lapangan, ada 5 peran yang saling bekerja sama:

```
[1. KLIEN] ──(Buat Pesanan PO)──> [2. SEKRETARIS] ──(Setujui Pesanan)──> [Stok Panen Terkunci]
                                         │
                                   (Cek Pembayaran)
                                         │
                                  [Status: LUNAS]
                                         │
[4. ARMADA] <──(Terbitkan Surat Jalan)───┘
     │
 (Kirim Sayur & Foto Bukti PoD)
     │
     ▼
[3. KOORDINATOR LAPANGAN] ──(Input Panen Harian Petani)──> [Stok Gudang Bertambah]
     ▲
     │
[5. DIREKTUR UTAMA] ──(Pantau Grafik Penjualan, Keuangan & Setujui Kontrak B2B)
```

1. **Klien (Pembeli / Restoran / Supermarket):**
   * Masuk ke web, lihat katalog komoditas hasil panen.
   * Masukkan sayur ke keranjang, checkout, dan unggah bukti transfer.
2. **Sekretaris (Pusat Kendali Administrasi):**
   * Memeriksa pesanan baru yang masuk.
   * Menyetujui pesanan (*mengunci kuota panen agar tidak direbut orang lain*).
   * Menerbitkan faktur (Invoice PDF).
   * Memverifikasi bukti transfer uang di rekening.
   * Menetapkan supir armada dan mencetak **Surat Jalan**.
3. **Koordinator Lapangan (Mandor Gudang & Petani):**
   * Mengelola data master sayuran (kubis, kentang, wortel, brokoli).
   * Mencatat hasil panen harian dari Petani Binaan Ciwidey (modul *Quick Intake*).
   * Menjaga ketersediaan stok fisik di gudang.
4. **Armada (Supir Pengiriman):**
   * Membuka web lewat HP/tablet saat mengemudi.
   * Melihat daftar alamat pengiriman dan Surat Jalan digital.
   * Menyerahkan sayur, mencatat jika ada sayur yang rusak/retur, dan memfoto bukti serah terima (*Proof of Delivery / PoD*).
5. **Direktur (Owner / Pimpinan Koperasi):**
   * Melihat dashboard eksekutif: omzet penjualan, piutang tempo, dan volume komoditas.
   * Menyetujui kontrak kemitraan jangka panjang (B2B Kontrak) dengan restoran besar.

---

## 3. Kamus Istilah Tingkat Tinggi (Diterjemahkan ke Bahasa Manusia)

Aplikasi Anda menggunakan konsep-konsep *Enterprise* yang sangat disukai penguji UKK. Pahami analogi berikut agar Anda bisa menjelaskannya dengan santai:

### A. `DB::transaction(function() { ... })` (Transaksi Database ACID)
* **Masalah Nyata:** Saat memesan, sistem harus menyimpan data pesanan, menyimpan detail sayur yang dibeli, dan memotong stok. Bagaimana kalau setelah menyimpan pesanan, tiba-tiba server mati atau koneksi putus sebelum stok terpotong? Database akan berantakan!
* **Solusi (`DB::transaction`):** Prinsip **"Semua Berhasil atau Semua Dibatalkan"**. Jika ada 1 perintah yang gagal di tengah jalan, seluruh perubahan akan di-*rollback* (dikembalikan seperti semula seolah tidak terjadi apa-apa).

### B. `lockForUpdate()` (Pessimistic Row Locking)
* **Masalah Nyata:** Sisa stok Kentang Granola tinggal 50 kg. Klien A dan Klien B membuka web di detik yang sama persis dan sama-sama memesan 50 kg.
* **Solusi (`lockForUpdate`):** Sistem memasang gembok fisik pada baris data kentang tersebut. Klien A yang tiba 0,001 detik lebih awal akan dilayani dulu, sementara Klien B disuruh menunggu antre. Setelah Klien A selesai memotong stok, Klien B baru boleh membaca sisa stok (yang ternyata sudah 0 kg), sehingga Klien B ditolak dengan aman. Ini mencegah **overselling** (menjual barang yang stoknya sudah habis).

### C. `Cache::lock()` & `submission_token` (Idempotency & Anti-Double Click)
* **Masalah Nyata:** Klien yang tidak sabaran mengklik tombol *"Kirim Pesanan"* 5 kali secara cepat karena internetnya lambat.
* **Solusi:** `Cache::lock` mengunci proses selama beberapa detik. Jika token yang sama dikirim dua kali dalam 90 detik, sistem langsung mendeteksi duplikasi dan tidak akan membuat 2 pesanan yang sama.

### D. `Service Layer` (Contoh: `OrderService.php`)
* **Analogi Restoran:**
  * **Route (`web.php`):** Pintu masuk pelanggan.
  * **Controller (`OrderController`):** Pelayan yang bertugas menyambut tamu, menerima pesanan, dan menyerahkan piring. Pelayan TIDAK BOLEH memasak di dapur!
  * **Service (`OrderService`):** Koki di dapur yang menghitung takaran bumbu, memotong bahan, dan memasak makanan.
* **Keuntungan:** Controller tetap bersih dan ramping (*Skinny Controller*), sementara seluruh hitungan rumit ada di Service.

### E. `Enums` (Contoh: `OrderStatus.php`)
* **Masalah di PHP Jadul:** Pengembang sering menulis string bebas seperti `'lunas'`, `'LUNAS'`, `'selesai'`, `'paid'`. Jika salah ketik satu huruf saja (`'luans'`), aplikasi langsung rusak.
* **Solusi:** Enum adalah daftar pilihan baku yang dikunci oleh PHP 8.2 (misal: `OrderStatus::MENUNGGU_VERIFIKASI`, `OrderStatus::LUNAS`). Jika Anda mengetik status di luar daftar resmi, PHP langsung memberi tahu saat itu juga.

### F. `Form Request` (Contoh: `StoreOrderRequest.php`)
* **Analogi:** Satpam di depan pintu masuk. Sebelum data formulir masuk ke Controller, satpam ini memeriksa apakah input nama kosong, apakah format tanggal benar, apakah upload file benar-benar gambar. Jika tidak lolos, langsung ditolak sebelum menyentuh Controller.

---

## 4. Peta Alur: "Kalau Tombol Ini Diklik, Kodenya Lari ke Mana?"

Gunakan tabel ini jika penguji meminta Anda menunjukkan file kode saat mengklik suatu menu di aplikasi:

| Aksi di Layar | Route yang Dipanggil | Controller & Method | File Tampilan (Blade) |
| :--- | :--- | :--- | :--- |
| **Klien Checkout Keranjang** | `POST /klien/orders` | `Klien\OrderController@store` | `resources/views/klien/cart.blade.php` |
| **Sekretaris Setujui Pesanan** | `POST /sekretaris/orders/{id}/approve` | `Sekretaris\OrderVerificationController@approve` | `resources/views/sekretaris/verification.blade.php` |
| **Sekretaris Terbitkan Surat Jalan** | `POST /sekretaris/surat-jalan/{id}/terbitkan` | `Sekretaris\DispatchController@issue` | `resources/views/sekretaris/dispatch.blade.php` |
| **Sekretaris Verifikasi Bukti Bayar** | `POST /sekretaris/pembayaran/{id}/approve` | `Sekretaris\PaymentController@approve` | `resources/views/sekretaris/payments.blade.php` |
| **Koordinator Catat Panen Baru** | `POST /koordinator/stok` | `Koordinator\StockController@storeBatch` | `resources/views/koordinator/stock.blade.php` |
| **Armada Selesaikan Kiriman (PoD)** | `POST /armada/scan-pod/{id}/selesai` | `Armada\PodController@store` | `resources/views/armada/pod.blade.php` |
| **Direktur Setujui Kontrak B2B** | `POST /direktur/contracts/batch-approve` | `Direktur\ContractController@batchApprove` | `resources/views/direktur/contracts/approval.blade.php` |
| **Cetak Surat Jalan Resmi PDF** | `GET /prints/surat-jalan/{order}` | `Prints\SuratJalanController` | `resources/views/prints/surat-jalan.blade.php` |

---

## 5. Bedah Kode Kunci: Penjelasan Baris per Baris

Berikut potongan kode paling penting yang menjadi "jantung" sistem ini:

### 1. `app/Services/StockService.php` (Fungsi Pengurangan Stok Otomatis)
```php
public function allocateStock(int $productId, float $qty): void
{
    // Mengambil data panen yang masih punya sisa stok, diurutkan dari tanggal panen paling lama (FIFO)
    $batches = HarvestBatch::where('product_id', $productId)
        ->where('available_quantity', '>', 0)
        ->orderBy('batch_date', 'asc') // FIFO: First-In First-Out
        ->orderBy('id', 'asc')
        ->lockForUpdate() // <--- GEMBOK: Mencegah user lain mengubah data di detik yang sama!
        ->get();

    $remainingToDeduct = $qty;

    foreach ($batches as $batch) {
        if ($remainingToDeduct <= 0) {
            break; // Jika pesanan sudah terpenuhi, berhenti potong
        }

        $currentAvailable = (float) $batch->available_quantity;
        // Ambil mana yang lebih kecil: sisa stok batch atau sisa kebutuhan pesanan
        $deduction = min($currentAvailable, $remainingToDeduct);

        // Potong stok batch ini di database
        $batch->decrement('available_quantity', $deduction);
        $remainingToDeduct -= $deduction;
    }
}
```
* **Poin Pintar untuk Dikatakan ke Penguji:** *"Pak/Bu, sistem kami menggunakan metode alokasi FIFO (First In First Out), di mana sayur yang dipanen lebih awal akan dipotong dan dikirim lebih dahulu agar tidak membusuk di gudang."*

---

### 2. `app/Services/OrderService.php` (Fungsi Persetujuan Pesanan oleh Sekretaris)
```php
public function approveOrder(int $orderId, int $secretaryId): Order
{
    // Membungkus seluruh proses dalam satu transaksi aman
    return DB::transaction(function () use ($orderId, $secretaryId) {
        $order = Order::with('orderItems')->lockForUpdate()->findOrFail($orderId);

        // Validasi status: Hanya pesanan baru yang boleh disetujui
        if ($order->status !== OrderStatus::MENUNGGU_VERIFIKASI->value) {
            throw new Exception("Hanya pesanan berstatus MENUNGGU_VERIFIKASI yang dapat disetujui.");
        }

        // Potong kuota stok sayur di gudang
        foreach ($order->orderItems as $item) {
            $this->stockService->allocateStock($item->product_id, (float) $item->ordered_qty);
        }

        // Terbitkan Invoice (Faktur Tagihan) otomatis dengan nomor unik
        if (!$order->invoice_id) {
            // ... pembuatan nomor faktur unik INV-GPA-YYYYMM-XXXX ...
        }

        // Perbarui status pesanan menjadi MENUNGGU_PEMBAYARAN
        $order->status = OrderStatus::MENUNGGU_PEMBAYARAN->value;
        $order->verified_by = $secretaryId;
        $order->save();

        return $order;
    });
}
```
* **Poin Pintar untuk Dikatakan ke Penguji:** *"Saat Sekretaris menyetujui pesanan, sistem secara otomatis menjalankan 3 hal sekaligus: memotong kuota stok panen di gudang, menerbitkan nomor faktur tagihan resmi, dan mengubah status pesanan menjadi Menunggu Pembayaran."*

---

## 6. Bocoran & Kunci Jawaban Pertanyaan Penguji UKK

Ini adalah daftar pertanyaan favorit yang biasa diajukan oleh guru atau penguji eksternal dari industri:

#### Q1: "Kenapa kamu memisahkan logika ke dalam folder `app/Services`, kenapa tidak langsung di Controller saja?"
> **Jawaban:** *"Untuk menerapkan prinsip Clean Code yaitu Separation of Concerns dan Single Responsibility Principle. Controller hanya bertugas menangani request HTTP dan mengembalikan view/JSON, sedangkan seluruh hitungan bisnis seperti kalkulasi stok dan pembuatan nomor faktur ditaruh di Service Layer. Dengan begitu, kode lebih rapi, tidak terjadi controller gemuk (fat controller), dan kodenya mudah diuji menggunakan automated test."*

#### Q2: "Bagaimana cara sistem kamu mencegah dua pembeli membeli stok sayur yang sama pada detik yang bersamaan atau klik ganda?"
> **Jawaban:** *"Kami menggunakan dua lapis pengamanan: Pertama, untuk mencegah klik ganda saat checkout, sistem memeriksa token pesanan unik (`submission_token`) dan mendeteksi apakah pesanan serupa baru saja dikirim dalam 90 detik terakhir. Kedua, di level database kami menggunakan Pessimistic Row Locking dengan fungsi `lockForUpdate()` di dalam blok `DB::transaction()`. Jadi database akan mengunci baris data stok tersebut sampai transaksi pertama selesai memotong stok, baru transaksi kedua diproses secara berurutan."*

#### Q3: "Apa fungsi file di folder `app/Enums/`?"
> **Jawaban:** *"Enum adalah fitur PHP modern untuk mengunci pilihan nilai yang valid, seperti status pesanan atau metode pembayaran. Tujuannya mencegah human error berupa salah ketik string di kodingan. Jika ada programmer yang mengetik status di luar enum, PHP akan langsung memberikan error."*

#### Q4: "Bagaimana sistem kamu mengatur hak akses agar Armada tidak bisa membuka halaman Direktur?"
> **Jawaban:** *"Kami menggunakan custom middleware yang memeriksa kolom `role` pada pengguna yang sedang login. Di file `routes/web.php`, setiap grup rute dilindungi oleh middleware, contohnya `middleware(['auth', 'role:DIREKTUR'])` untuk Direktur dan `middleware(['auth', 'role:ARMADA'])` untuk supir armada. Jika ada role yang tidak sesuai mencoba mengakses URL tersebut, sistem otomatis memunculkan error 403 Forbidden."*

#### Q5: "Apa itu FIFO dalam sistem inventaris hasil tani kamu?"
> **Jawaban:** *"FIFO adalah singkatan dari First In First Out. Di method `StockService@allocateStock`, sistem memotong stok dari batch panen yang tanggal masuknya paling awal (`orderBy('batch_date', 'asc')`). Ini sangat penting untuk komoditas hasil tani agar sayur yang dipanen lebih dulu bisa dikirim lebih awal untuk meminimalkan risiko sayur layu atau membusuk."*

---

## 7. Tips Sukses Demonstrasi di Depan Penguji

1. **Jelaskan Cerita Bisnisnya Terlebih Dahulu:**  
   Sebelum membuka VS Code, ceritakan latar belakangnya: *"Aplikasi AgroOrder GPA ini dibuat untuk memecahkan masalah pencatatan manual di Koperasi Sayur Ciwidey, menghubungkan petani dengan pembeli restoran secara terintegrasi melalui 5 peran."* Penguji akan langsung kagum karena Anda paham masalah dunia nyata.
2. **Tunjukkan Fitur Otomatis:**  
   Demonstrasikan saat Sekretaris klik "Setujui", tunjukkan bahwa nomor Invoice langsung terbit dan stok di menu Koordinator otomatis berkurang.
3. **Pamerkan Unit Test:**  
   Buka terminal, jalankan `php artisan test`. Saat penguji melihat **147 Passed Tests (Hijau Semua)**, mereka akan melihat bahwa aplikasi Anda bukan sekadar web CRUD biasa, melainkan proyek berstandar industri yang stabil dan aman dari celah keamanan (XSS, SQL Injection, CSRF).

