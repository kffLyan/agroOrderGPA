# 🌱 AgroOrder GPA

**Sistem Informasi Katalog, Manajemen Stok, dan Pemesanan Hasil Pertanian**

AgroOrder GPA merupakan aplikasi berbasis web yang dikembangkan untuk mendukung digitalisasi proses bisnis **Koperasi Tani Green Pasundan Agriculture (GPA)** yang berlokasi di Ciwidey, Kabupaten Bandung.

Aplikasi ini dirancang untuk membantu pengelolaan katalog komoditas pertanian, ketersediaan stok, pemesanan, pembayaran, pengiriman, serta pengelolaan data operasional dalam satu sistem terintegrasi.

---

## 📌 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Latar Belakang](#-latar-belakang)
- [Tujuan Pengembangan](#-tujuan-pengembangan)
- [Fitur Utama](#-fitur-utama)
- [Hak Akses Pengguna](#-hak-akses-pengguna)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Struktur Proyek](#-struktur-proyek)
- [Pengujian](#-pengujian)
- [Tim Pengembang](#-tim-pengembang)

---

## 📖 Tentang Proyek

AgroOrder GPA merupakan sistem informasi yang berfokus pada pengelolaan rantai proses pemesanan hasil pertanian, mulai dari pengelolaan komoditas hingga proses pengiriman kepada klien.

Sistem ini menyediakan beberapa modul yang saling terintegrasi untuk membantu pihak internal GPA dan klien dalam menjalankan aktivitas operasional.

**Informasi Proyek:**

| Keterangan | Detail |
|---|---|
| Nama Aplikasi | AgroOrder GPA |
| Jenis Aplikasi | Sistem Informasi Berbasis Web |
| Instansi | Koperasi Tani Green Pasundan Agriculture |
| Lokasi | Ciwidey, Kabupaten Bandung |
| Framework | Laravel 12 |
| Bahasa Pemrograman | PHP 8.2+ |
| Database | SQLite / MySQL (sesuai konfigurasi) |
| Status | Pengembangan |

---

## 🎯 Latar Belakang

Dalam kegiatan operasional GPA, pengelolaan informasi stok dan pemesanan hasil pertanian membutuhkan koordinasi antara pihak koperasi, koordinator lapangan, armada, dan klien.

Proses penyampaian informasi yang masih bergantung pada komunikasi melalui pesan dapat menyebabkan keterlambatan informasi, terutama ketika banyak permintaan masuk dalam waktu bersamaan. Informasi stok juga membutuhkan konfirmasi dari pihak GPA sehingga klien tidak selalu dapat mengetahui ketersediaan komoditas secara langsung.

Kondisi tersebut dapat menghambat proses pemesanan dan koordinasi operasional.

Oleh karena itu, AgroOrder GPA dikembangkan sebagai solusi digital untuk mengintegrasikan pengelolaan katalog, stok, pemesanan, pembayaran, dan pengiriman agar proses bisnis menjadi lebih terstruktur.

---

## 🚀 Tujuan Pengembangan

1. Mempermudah klien dalam melihat katalog dan informasi ketersediaan komoditas.
2. Membantu GPA mengelola stok hasil pertanian secara terpusat.
3. Mempermudah proses pencatatan dan pengelolaan pesanan.
4. Mengintegrasikan proses pembayaran dan verifikasi transaksi.
5. Membantu koordinasi pengiriman melalui pengelolaan armada.
6. Menyediakan informasi operasional yang lebih terstruktur bagi pihak terkait.

---

## ✨ Fitur Utama

### 1. Manajemen Pengguna
- Pengelolaan akun berdasarkan peran pengguna.
- Pengaturan hak akses sesuai tanggung jawab masing-masing.
- Pengelolaan informasi profil pengguna.

### 2. Katalog dan Komoditas
- Menampilkan katalog hasil pertanian.
- Pengelolaan informasi komoditas.
- Informasi harga dan ketersediaan produk.
- Pengelolaan data produk oleh pihak internal.

### 3. Manajemen Stok
- Pengelolaan stok hasil pertanian.
- Pencatatan batch hasil panen.
- Pengalokasian stok untuk memenuhi pesanan.
- Pengelolaan stok berdasarkan ketersediaan komoditas.

### 4. Manajemen Pemesanan
- Pengajuan pesanan oleh klien.
- Pengelolaan detail pesanan.
- Verifikasi pesanan oleh sekretaris.
- Pengelolaan status pesanan.
- Penerbitan informasi tagihan atau invoice.

### 5. Manajemen Pembayaran
- Pengunggahan bukti pembayaran oleh klien.
- Verifikasi pembayaran oleh pihak GPA.
- Pencatatan riwayat pembayaran.
- Pengelolaan status pembayaran.

### 6. Manajemen Pengiriman
- Pengelolaan penugasan armada.
- Pengelolaan informasi kendaraan.
- Pemantauan status pengiriman.
- Pencatatan bukti serah terima (*Proof of Delivery*).
- Pencatatan komplain dan retur barang.

### 7. Dashboard dan Laporan
- Menampilkan ringkasan aktivitas operasional.
- Menampilkan informasi pesanan dan transaksi.
- Menyediakan informasi sesuai hak akses pengguna.

---

## 👥 Hak Akses Pengguna

AgroOrder GPA menerapkan pembagian hak akses berdasarkan lima peran utama.

| Role | Tanggung Jawab |
|---|---|
| DIREKTUR | Memantau aktivitas dan informasi operasional perusahaan. |
| SEKRETARIS | Mengelola pesanan, pembayaran, invoice, dan administrasi operasional. |
| KOORDINATOR | Mengelola aktivitas lapangan, komoditas, dan stok hasil pertanian. |
| ARMADA | Mengelola tugas pengiriman dan bukti serah terima barang. |
| KLIEN | Melihat katalog, melakukan pemesanan, dan mengelola pembayaran. |

Setiap pengguna memperoleh akses sesuai dengan peran dan kewenangannya.

### Akun Pengujian Multi-Role Default

Untuk kebutuhan evaluasi dan pengujian sistem, seluruh akun pengujian menggunakan kata sandi standar: **`password123`**

| Peran (Role) | Alamat Email | Peruntukan & Hak Akses Utama |
|---|---|---|
| DIREKTUR | `direktur@greenpasundan.id` | Dasbor eksekutif, buku besar piutang (AR Aging), master freeze |
| SEKRETARIS | `sekre@greenpasundan.id` | Verifikasi pesanan, cetak Surat Jalan A5 & Struk Thermal, faktur |
| KOORDINATOR | `koordinator@greenpasundan.id` | Intake batch panen petani, neraca stok fisik, mutasi barang |
| ARMADA | `armada@greenpasundan.id` | Daftar kirim kurir reefer, upload foto Proof of Delivery (PoD) |
| KLIEN B2B | `aerofood@client.id` | Pemesanan sayur kontrak B2B, Term of Payment (TOP), e-faktur |
| KLIEN REGULER | `hendra@kateringbarokah.com` | Pemesanan harga dasar retail, unggah bukti transfer per PO |

---

## 🛠 Teknologi yang Digunakan

### Backend
- **PHP 8.2+** — Bahasa pemrograman backend.
- **Laravel 12** — Framework utama aplikasi.
- **Eloquent ORM** — Pengelolaan interaksi database.
- **Laravel Blade** — Template engine untuk tampilan aplikasi.

### Frontend
- **HTML5** — Struktur halaman.
- **CSS3** — Pengaturan tampilan.
- **JavaScript** — Interaksi pada halaman.
- **Tailwind CSS** — Framework CSS.
- **Alpine.js** — Interaksi antarmuka.
- **Vite** — Asset bundling dan development server.

### Database
- **SQLite / MySQL** — Sistem manajemen basis data sesuai konfigurasi lingkungan aplikasi.

### Development Tools
- Composer
- Node.js dan NPM
- Git dan GitHub
- Visual Studio Code

---

## 💻 Persyaratan Sistem

Pastikan perangkat sudah memiliki:

| Software | Persyaratan |
|---|---|
| PHP | 8.2 atau lebih baru |
| Composer | Versi yang kompatibel dengan Laravel 12 |
| Node.js | Versi yang kompatibel dengan Vite |
| NPM | Tersedia |
| Database | SQLite atau MySQL |
| Git | Terpasang |

Ekstensi PHP yang dibutuhkan mengikuti persyaratan Laravel dan konfigurasi proyek.

---

## ⚙️ Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/kffLyan/AgroOrderGPA.git
```

### 2. Masuk ke Direktori Proyek

```bash
cd AgroOrderGPA
```

### 3. Install Dependency PHP

```bash
composer install
```

### 4. Install Dependency Frontend

```bash
npm install
```

### 5. Buat File Environment

```bash
cp .env.example .env
```

Untuk Windows Command Prompt, dapat menggunakan:

```cmd
copy .env.example .env
```

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Konfigurasi Database

Sesuaikan konfigurasi database pada file `.env`.

Contoh konfigurasi MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agroorder_gpa
DB_USERNAME=root
DB_PASSWORD=
```

Jika menggunakan SQLite, sesuaikan `DB_CONNECTION` dan lokasi database dengan konfigurasi proyek.

### 8. Jalankan Migrasi dan Seeder

```bash
php artisan migrate --seed
```

Seeder digunakan untuk menyiapkan data awal aplikasi sesuai implementasi yang tersedia.

### 9. Buat Symbolic Link Storage

```bash
php artisan storage:link
```

---

## ▶️ Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite pada terminal terpisah:

```bash
npm run dev
```

Buka aplikasi melalui browser:

```text
http://127.0.0.1:8000
```

Untuk kebutuhan build asset:

```bash
npm run build
```

---

## 📁 Struktur Proyek

Berikut gambaran direktori utama aplikasi Laravel:

```text
AgroOrderGPA/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   │
│   ├── Models/
│   ├── Services/
│   └── Providers/
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── docs/
│
├── public/
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── console.php
│
├── storage/
│
├── tests/
│
├── .env.example
├── artisan
├── composer.json
├── package.json
└── README.md
```

Struktur di atas merupakan gambaran direktori utama. Beberapa subdirektori dapat berbeda mengikuti perkembangan implementasi proyek.

---

## 🧪 Pengujian

AgroOrder GPA menggunakan PHPUnit melalui fasilitas testing Laravel.

Untuk menjalankan seluruh pengujian:

```bash
php artisan test
```

Untuk menjalankan pengujian berdasarkan nama atau kelompok tertentu:

```bash
php artisan test --filter=NamaTest
```

Hasil pengujian akan menunjukkan skenario yang berhasil maupun yang masih mengalami kegagalan.

---

## 👨💻 Tim Pengembang

AgroOrder GPA dikembangkan sebagai proyek kolaboratif oleh siswa Program Keahlian Pengembangan Perangkat Lunak dan Gim (PPLG).

| Nama | Tanggung Jawab |
|---|---|
| Muhammad Kafka Lyandra Pratama | Backend Development |
| Rangga Wisesha | Frontend Development |

*Silakan sesuaikan nama dan tanggung jawab berdasarkan susunan tim pengembang yang sebenarnya.*

---

## 📄 Lisensi

Proyek AgroOrder GPA dikembangkan untuk kebutuhan pembelajaran dan implementasi sistem informasi pada Koperasi Tani Green Pasundan Agriculture.

Penggunaan, distribusi, dan pengembangan lebih lanjut mengikuti ketentuan yang ditetapkan oleh pemilik proyek.

---

**AgroOrder GPA**  
*Digitalisasi Pengelolaan dan Pemesanan Hasil Pertanian.*
