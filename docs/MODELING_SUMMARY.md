# SYSTEM MODELING SUMMARY & ARCHITECTURE REFERENCE

**Proyek:** AgroOrder GPA — Sistem Informasi Katalog, Manajemen Stok, dan Pemesanan Hasil Tani  
**Target Pengguna:** Koperasi Tani Green Pasundan Agriculture  
**Lokasi:** Ciwidey, Kabupaten Bandung  
**Dokumen:** `docs/MODELING_SUMMARY.md`

---

## 1. Perubahan Alur Bisnis Utama (*Business Process Overhaul*)

### 1.1. Skema Pembayaran di Muka (*Upfront Payment*)

- Sistem tidak lagi mewajibkan staf gudang melakukan penimbangan fisik ketika pesanan pembeli masuk.
- Nilai total tagihan (`grand_total`) ditetapkan berdasarkan kuantitas pesanan awal (`ordered_qty`).
- Setelah draf pesanan disetujui Sekretaris, sistem menerbitkan **Invoice Tagihan dalam format PDF**.
- Kuantitas pesanan menjadi dasar perhitungan tagihan awal. Penyesuaian hanya dilakukan melalui mekanisme retur atau koreksi yang tercatat sesuai aturan bisnis.

### 1.2. Syarat Penerbitan Surat Jalan

- Surat Jalan fisik hanya dapat diterbitkan oleh Sekretaris.
- Penerbitan Surat Jalan dilakukan setelah pembayaran dinyatakan lunas dan diverifikasi oleh Sekretaris berdasarkan buku mutasi rekening bank atau QRIS secara manual.
- Sistem harus membatasi akses penerbitan Surat Jalan sebelum status pembayaran terverifikasi lunas.
- Setiap penerbitan Surat Jalan harus tercatat dalam audit trail sistem.

### 1.3. Peran Koordinator Lapangan

Koordinator Lapangan berfokus pada pengelolaan pasokan dan persediaan (*supply management*), bukan pemrosesan transaksi pesanan pembeli.

Tanggung jawabnya meliputi:

- Mengelola data master komoditas melalui tabel `products`.
- Mencatat pasokan panen harian dari Petani Binaan atau sumber pasokan luar melalui modul **Quick Intake Ledger**.
- Mencatat informasi pasokan pada tabel `harvest_batches`, termasuk `source_type`, `supplier_name`, `initial_quantity`, `grade`, dan `qc_notes`.
- Memastikan jumlah persediaan aktif (`available_quantity`) diperbarui secara konsisten.

Koordinator tidak memiliki kewenangan untuk menyetujui pembayaran, menerbitkan invoice, Menerbitkan Surat jalan atau memproses transaksi pesanan pembeli.

---

## 2. Pemetaan Artefak Pemodelan dan UML Modular

### 2.1. Use Case Diagram

**Berkas:** `use_case_GPA_v1.0_2.drawio`

Diagram mencakup lima aktor utama:

1. `Klien`
2. `Sekretaris`
3. `Koordinator`
4. `Armada`
5. `Direktur`

Ruang lingkup sistem difokuskan pada rantai pasok hilir, mulai dari katalog, pengelolaan pasokan, pemesanan, pembayaran, hingga pengiriman.

Seluruh fitur manajemen agronomi atau kebun yang berada di luar ruang lingkup sistem (*out of scope*) telah dikeluarkan.

### 2.2. Activity Diagram Modular

**Berkas:** `Activity_Diagram_GPA_v1.0_2.drawio`

#### AD-01 — Pemesanan dan Penerbitan Invoice

Alur utama:

1. Klien menelusuri katalog komoditas.
2. Klien memilih produk dan memasukkan kuantitas pesanan.
3. Sistem memvalidasi kuantitas terhadap ketentuan minimum pemesanan (*minimum order*).
4. Sistem memeriksa ketersediaan stok.
5. Sistem melakukan penguncian baris data (*row-level locking*) ketika diperlukan untuk mencegah persaingan transaksi yang mengubah stok secara bersamaan.
6. Klien mengirimkan draf pesanan.
7. Sekretaris memeriksa dan menyetujui pesanan.
8. Sistem menetapkan `grand_total` dan menerbitkan Invoice Tagihan PDF.
9. Status pesanan berubah menjadi `MENUNGGU_PEMBAYARAN`.

#### AD-02 — Pembayaran dan Verifikasi

Alur utama:

1. Klien melakukan pembayaran melalui transfer bank atau QRIS.
2. Klien mengunggah bukti pembayaran.
3. Sistem mencatat pembayaran beserta bukti dan status verifikasinya.
4. Sekretaris mencocokkan pembayaran dengan buku mutasi rekening bank atau catatan transaksi QRIS.
5. Sekretaris menerima atau menolak bukti pembayaran berdasarkan hasil pemeriksaan.
6. Jika pembayaran terverifikasi lunas, sistem memperbarui status pesanan menjadi `LUNAS`.
7. Sistem membuka akses penerbitan Surat Jalan bagi Sekretaris.

#### AD-03 — Pengiriman dan Serah Terima

Alur utama:

1. Sekretaris menetapkan armada pengiriman.
2. Sekretaris memasukkan nomor kendaraan dan menerbitkan Surat Jalan PDF.
3. Armada memeriksa muatan dan memulai pengiriman melalui perangkat seluler.
4. Sistem mencatat waktu keberangkatan.
5. Armada mengantarkan pesanan ke alamat tujuan.
6. Armada melakukan serah terima barang kepada Klien.
7. Jika ditemukan sayuran cacat atau ketidaksesuaian barang, Armada mencatat retur beserta berat dan alasannya.
8. Armada mengunggah foto *Proof of Delivery* (PoD).
9. Sistem mencatat waktu kedatangan dan menyelesaikan transaksi sesuai kelengkapan serah terima serta pencatatan retur.

### 2.3. User Flow dan Flowchart Aktor

**Berkas:**
- `User_Flow_GPA_v1.0_2.drawio`
- `Flowchart_Aktor_GPA_v1.0_2.drawio`

Kedua artefak digunakan sebagai referensi navigasi dan logika proses setiap peran.

Cakupan pemodelan meliputi:

- Rute navigasi layar berdasarkan peran pengguna.
- Validasi masukan (*input validation*).
- Penanganan kesalahan (*error handling*).
- Peringatan pembatasan akses.
- Percabangan keputusan `if-else`.
- Validasi status transaksi sebelum suatu tindakan dijalankan.

---

## 3. Skema Basis Data

Basis data inti terdiri dari **8 tabel relasional**, menggunakan InnoDB dan dirancang mengikuti prinsip normalisasi hingga Third Normal Form (3NF).

### 3.1. Tabel `users`

Menyimpan data akun pengguna dan profil yang diperlukan sistem.

Kolom atau atribut konseptual:

- Identitas pengguna.
- Kredensial autentikasi.
- Peran pengguna.
- Jenis klien.
- Profil perusahaan.
- Data penanggung jawab (*Person in Charge* / PIC).

Nilai peran yang direncanakan:

- `KLIEN`
- `SEKRETARIS`
- `KOORDINATOR`
- `ARMADA`
- `DIREKTUR`

Jenis klien:

- `REGULER`
- `B2B_KONTRAK`

### 3.2. Tabel `products`

Menyimpan master data komoditas hasil tani.

Atribut utama:

- `sku`
- `name`
- `unit`
- `grade`
- `base_price`
- `minimum_order`

Tabel ini menjadi referensi katalog produk dan dasar validasi pemesanan.

### 3.3. Tabel `contracts`

Menyimpan perjanjian kerja sama harga dan volume dengan mitra korporat B2B.

Atribut utama:

- Referensi klien.
- Harga kontrak per kilogram.
- Volume komitmen.
- Periode kontrak.
- Durasi *Terms of Payment* (TOP).
- Status kontrak.

Aturan harga kontrak harus digunakan ketika pesanan memenuhi ketentuan kontrak yang berlaku.

### 3.4. Tabel `harvest_batches`

Menyimpan buku besar pasokan panen harian melalui Quick Intake Ledger.

Atribut utama:

- `source_type`
- `supplier_name`
- `initial_quantity`
- `grade`
- `qc_notes`
- `available_quantity`

Tabel ini menjadi dasar pencatatan penerimaan pasokan dan pemantauan kuantitas yang tersedia.

Perubahan kuantitas harus dilakukan secara konsisten agar tidak terjadi stok negatif atau pemakaian stok yang sama oleh transaksi bersamaan.

### 3.5. Tabel `invoices`

Menyimpan metadata dan referensi dokumen tagihan yang diterbitkan sistem.

Cakupan:

- Nomor invoice.
- Referensi pesanan atau periode konsolidasi.
- Tanggal penerbitan.
- Nilai tagihan.
- Status invoice.
- Lokasi berkas PDF.
- Informasi jatuh tempo apabila berlaku.

Invoice dapat diterbitkan untuk transaksi pembayaran di muka maupun konsolidasi bulanan sesuai ketentuan TOP bagi mitra B2B.

### 3.6. Tabel `orders`

Merupakan tabel pusat transaksi pemesanan.

Atribut utama:

- `order_number`
- `target_delivery_date`
- `delivery_address`
- `grand_total`
- `status`
- Nomor Surat Jalan.
- `driver_id`
- Waktu keberangkatan.
- Waktu kedatangan.
- `pod_photo_url`

Tabel ini menyimpan informasi utama pesanan, status proses bisnis, penugasan pengiriman, dan bukti penyelesaian.

### 3.7. Tabel `order_items`

Menyimpan rincian produk dalam setiap pesanan serta pencatatan retur fisik.

Atribut utama:

- Referensi pesanan.
- Referensi produk.
- `ordered_qty`
- `unit_price`
- `subtotal`
- `returned_weight`
- `return_reason`

Rincian retur dicatat pada tingkat item agar setiap komoditas yang dikembalikan dapat ditelusuri secara terpisah.

Sistem tidak perlu menambahkan kolom penimbangan gudang yang redundan apabila data tersebut tidak digunakan oleh alur bisnis yang telah ditetapkan.

### 3.8. Tabel `payments`

Menyimpan catatan pembayaran manual dan proses verifikasinya.

Atribut utama:

- Referensi pesanan atau invoice.
- Metode pembayaran.
- Nominal pembayaran.
- Waktu pembayaran.
- `proof_url`
- Status verifikasi.
- `verification_note`
- Referensi pengguna yang melakukan verifikasi.
- Waktu verifikasi.

Bukti transfer atau QRIS harus dapat ditelusuri dan status verifikasi harus memiliki riwayat yang memadai untuk kebutuhan audit.

### 3.9. Aturan Integritas Basis Data

Implementasi basis data harus memastikan:

- Relasi antarentitas menggunakan *foreign key* yang sesuai.
- Nilai kuantitas, harga, dan nominal uang menggunakan tipe data numerik yang tepat.
- Nomor pesanan dan nomor invoice memiliki aturan keunikan.
- Stok tidak menjadi negatif akibat transaksi bersamaan.
- Perubahan status transaksi mengikuti aturan bisnis.
- Operasi yang melibatkan beberapa perubahan data penting menggunakan transaksi basis data (*database transaction*).
- Data pembayaran dan perubahan status memiliki jejak audit yang memadai.

Catatan: normalisasi 3NF harus diverifikasi terhadap skema fisik final, bukan hanya berdasarkan jumlah tabel.

---

## 4. Siklus Status Transaksi (`orders.status`)

Status transaksi utama mengikuti alur berikut:

```text
MENUNGGU_VERIFIKASI
        |
        | Sekretaris menyetujui pesanan
        | Sistem menerbitkan Invoice PDF
        v
MENUNGGU_PEMBAYARAN
        |
        | Klien mengunggah bukti pembayaran
        | Sekretaris memverifikasi pembayaran lunas
        v
      LUNAS
        |
        | Sekretaris menetapkan armada
        | Sekretaris menerbitkan Surat Jalan PDF
        v
    SIAP_KIRIM
        |
        | Armada memulai pengiriman
        | Sistem mencatat waktu keberangkatan
        v
DALAM_PENGIRIMAN
        |
        | Serah terima barang
        | Pencatatan retur jika ada
        | Pengunggahan foto PoD
        v
 SELESAI / SELESAI_CATATAN
```

### 4.1. Definisi Status

| Status | Definisi |
|---|---|
| `MENUNGGU_VERIFIKASI` | Pesanan telah diajukan dan menunggu pemeriksaan Sekretaris. |
| `MENUNGGU_PEMBAYARAN` | Pesanan telah disetujui dan invoice telah diterbitkan. |
| `LUNAS` | Pembayaran telah diverifikasi lunas oleh Sekretaris. |
| `SIAP_KIRIM` | Armada telah ditetapkan dan Surat Jalan telah diterbitkan. |
| `DALAM_PENGIRIMAN` | Pesanan sedang dalam proses pengantaran. |
| `SELESAI` | Pengiriman selesai tanpa catatan retur atau masalah yang perlu ditindaklanjuti. |
| `SELESAI_CATATAN` | Pengiriman selesai dengan catatan, misalnya retur atau ketidaksesuaian barang. |

Perubahan status hanya boleh dilakukan oleh peran yang berwenang atau oleh sistem sebagai hasil proses yang tervalidasi.

Status pembayaran harus dibedakan dari status pesanan. Pesanan berstatus `LUNAS` tidak otomatis berarti pengiriman telah selesai.

---

## 5. Aturan Bisnis dan Keamanan yang Wajib Dipertahankan

1. **Invoice:** Diterbitkan setelah pesanan disetujui Sekretaris.
2. **Pembayaran:** Bukti pembayaran yang diunggah Klien tidak otomatis menjadikan pesanan lunas.
3. **Verifikasi:** Hanya Sekretaris yang berwenang mengesahkan pembayaran berdasarkan pemeriksaan manual.
4. **Surat Jalan:** Tidak dapat diterbitkan sebelum pembayaran terverifikasi lunas.
5. **Otorisasi:** Setiap endpoint dan halaman harus memeriksa autentikasi, peran, dan kewenangan terhadap data yang diakses.
6. **Stok:** Perubahan stok harus konsisten dan aman terhadap transaksi bersamaan.
7. **Retur:** Retur harus mencatat kuantitas atau berat yang dikembalikan, alasan, dan pesanan terkait.
8. **Proof of Delivery:** Bukti pengiriman harus terhubung dengan pesanan yang benar.
9. **Audit:** Tindakan penting, khususnya verifikasi pembayaran, penerbitan Surat Jalan, perubahan status, dan pencatatan retur, harus dapat ditelusuri.
10. **Konsistensi:** Antarmuka, backend, dan basis data harus menerapkan aturan bisnis yang sama.

---

## 6. Referensi Berkas Pemodelan

| Artefak | Nama Berkas |
|---|---|
| Use Case Diagram | `use_case_GPA_v1.0_2.drawio` |
| Activity Diagram | `Activity_Diagram_GPA_v1.0_2.drawio` |
| User Flow | `User_Flow_GPA_v1.0_2.drawio` |
| Flowchart Aktor | `Flowchart_Aktor_GPA_v1.0_2.drawio` |
| Ringkasan Pemodelan | `docs/MODELING_SUMMARY.md` |

---

## 7. Instruksi untuk Asisten Koding

Dokumen ini merupakan referensi utama konsistensi arsitektur AgroOrder GPA.

Saat mengembangkan atau memodifikasi sistem:

1. Pelajari dokumen ini sebelum mengubah alur bisnis, skema basis data, atau hak akses.
2. Pertahankan pemisahan tanggung jawab antara Klien, Sekretaris, Koordinator, Armada, dan Direktur.
3. Jangan mengembalikan proses penimbangan gudang sebagai syarat pemrosesan pesanan.
4. Jangan mengizinkan Koordinator memproses transaksi pesanan pembeli.
5. Jangan mengizinkan penerbitan Surat Jalan sebelum pembayaran terverifikasi lunas.
6. Jangan menganggap unggahan bukti transfer sebagai verifikasi pembayaran otomatis.
7. Pastikan setiap perubahan kode tetap sesuai dengan aturan status, relasi basis data, dan ketentuan keamanan.
8. Jika implementasi yang sudah ada bertentangan dengan dokumen ini, identifikasi konflik terlebih dahulu sebelum mengubah kode.
9. Jika terdapat kebutuhan baru yang belum tercakup, jelaskan dampaknya terhadap proses bisnis, UML, dan skema basis data sebelum menerapkannya.
10. Jangan mengasumsikan detail teknis yang belum didefinisikan. Tandai keputusan yang masih memerlukan konfirmasi.
11. Jika ada data yang tidak sesuai dengan alur bisnis, jangan mengubah data tersebut tanpa persetujuan.

**Prinsip utama:** Seluruh implementasi AgroOrder GPA harus konsisten dengan alur bisnis, otorisasi peran, integritas persediaan, verifikasi pembayaran, dan dokumentasi pemodelan yang telah ditetapkan.