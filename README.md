# 🌿 AgroOrder GPA — Sistem Rantai Pasok & Tata Kelola Agribisnis B2B

[![Release](https://img.shields.io/badge/Release-v1.0.0--rc-0D9488?style=for-the-badge)](https://github.com)
[![Build Status](https://img.shields.io/badge/Tests-208%20Passed-10B981?style=for-the-badge)](https://github.com)
[![Security Audit](https://img.shields.io/badge/Security-Protected%20(ACID%20%7C%20Bcrypt%20%7C%20Anti--IDOR)-3B82F6?style=for-the-badge)](https://github.com)
[![Framework](https://img.shields.io/badge/Laravel-11%20MVC-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-slate?style=for-the-badge)](LICENSE)

> **Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak / PPLG — Tahun Ajaran 2026/2027**  
> **Tema:** Agribusiness Enterprise Supply Chain & Governance  
> **Mitra Industri:** Koperasi Produsen Agro Green Pasundan (Ciwidey, Kab. Bandung)

---

## 📌 1. Latar Belakang Masalah & Solusi

Sebelum digitalisasi, Koperasi Produsen Agro Green Pasundan (GPA) menghadapi 3 kendala operasional utama:
1. **Pemesanan Manual via WhatsApp:** Tonase dan pesanan klien komersial (hotel, katering penerbangan, restoran) sering tercecer dan memicu salah kirim.
2. **Double Selling & Stok Minus:** Pesanan diterima melebihi ketersediaan panen fisik di gudang packing house.
3. **Risiko Piutang Macet (Bad Debt):** Klien memesan tanpa pemantauan limit pagu kredit (*credit limit*) dan umur piutang (*AR Aging*) yang terukur.

**AgroOrder GPA** hadir sebagai solusi sistem rantai pasok agribisnis B2B terpadu dengan 5 tingkatan peran:
- **Klien (Mitra Kontrak & Reguler):** Katalog komoditas real-time, harga kontrak terkunci, PO otomatis, dan upload bukti bayar aman (Anti-IDOR).
- **Sekretariat:** Verifikasi pesanan, otomasi penerbitan faktur (`INV-GPA`), dan cetak Surat Jalan (A5 NCR) serta Struk Thermal POS.
- **Koordinator Gudang:** Penerimaan batch panen petani binaan, kontrol kualitas (QC Grade), dan neraca stok fisik.
- **Armada Logistik:** Manajemen rute pengantaran dan digital Proof of Delivery (PoD).
- **Direktur Eksekutif:** Pemantauan buku besar piutang (AR Aging), otorisasi kontrak B2B, dan kebijakan *Master Freeze*.

---

## 🛠️ 2. Tech Stack & Arsitektur Sistem

- **Backend:** PHP 8.2+ dengan **Laravel 11 (MVC Engine)**, Service Layer Architecture (`StockService`, `OrderService`, `TataKelolaService`, `ArmadaService`).
- **Database:** MySQL / MariaDB (InnoDB Engine, Terstandarisasi Bentuk Normal Ketiga / 3NF).
- **Frontend & UI/UX:** Blade Templating, Tailwind CSS, Alpine.js, Responsive Mobile Layout, Monokrom GPA Branding.
- **Security:**
  - Enkripsi password satu arah via **Bcrypt 12 rounds**.
  - Perlindungan SQL Injection via **PDO Prepared Statements** & Eloquent ORM.
  - Validasi ketat sisi server melalui **Laravel FormRequest**.
  - Upload file bukti transfer aman dengan whitelist MIME-Type (`jpeg, jpg, png, pdf`) dan penamaan acak (UUID hash).
  - Proteksi **Anti-IDOR** (Insecure Direct Object Reference) pada faktur dan dokumen transaksi.
  - Pemotongan stok komoditas dengan penguncian pesimistik `lockForUpdate()` dalam `DB::transaction`.

---

## 🚀 3. Panduan Instalasi Lokal (Quick Setup)

Ikuti langkah-langkah berikut untuk menjalankan sistem di lingkungan pengembang lokal:

### Prasyarat Sistem:
- PHP $\ge$ 8.2 & Composer
- MySQL Server (Laragon / XAMPP)
- Node.js & NPM (Opsional untuk build assets)
- Git CLI

### Langkah Instalasi:
```bash
# 1. Klon repositori ini
git clone https://github.com/kelompok-rpl/agroOrderGPA.git
cd agroOrderGPA

# 2. Pasang dependensi PHP
composer install

# 3. Siapkan berkas environment
cp .env.example .env

# 4. Generate Application Key
php artisan key:generate

# 5. Konfigurasi basis data di berkas .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=agroorder_gpa
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Jalankan migrasi dan data seeder bawaan
php artisan migrate:fresh --seed

# 7. Hubungkan symlink storage untuk berkas bukti bayar & PoD
php artisan storage:link

# 8. Jalankan server lokal
php artisan serve
```

Akses aplikasi pada peramban Anda melalui alamat: `http://localhost:8000`.

---

## 🔌 4. Kontinjensi Server Offline LAN (Darurat Ujian)

Jika koneksi internet sekolah terputus saat asesmen berlangsung, jalankan skrip cadangan:
```bash
# Jalankan skrip otomatis dari root folder:
run_local_offline.bat
```
Skrip ini akan mendeteksi IP lokal komputer Anda dan menjalankan `php artisan serve --host 0.0.0.0 --port 8000`. Penguji dapat mengakses sistem melalui WiFi hotspot lokal tanpa memerlukan akses internet publik.

---

## 🔑 5. Akun Pengujian Multi-Role Default

Semua akun pengujian menggunakan kata sandi standar: **`password123`**

| Peran (Role) | Alamat Email | Peruntukan & Hak Akses Utama |
| :--- | :--- | :--- |
| **Direktur** | `direktur@greenpasundan.id` | Dasbor eksekutif, buku besar piutang (AR Aging), master freeze |
| **Sekretaris** | `sekre@greenpasundan.id` | Verifikasi pesanan, cetak Surat Jalan A5 & Struk Thermal, faktur |
| **Koordinator** | `koordinator@greenpasundan.id` | Intake batch panen petani, neraca stok fisik, mutasi barang |
| **Armada** | `armada@greenpasundan.id` | Daftar kirim kurir reefer, upload foto Proof of Delivery (PoD) |
| **Klien B2B** | `aerofood@client.id` | Pemesanan sayur kontrak B2B, Term of Payment (TOP), e-faktur |
| **Klien Reguler** | `hendra@kateringbarokah.com` | Pemesanan harga dasar retail, unggah bukti transfer per PO |

---

## 🧪 6. Pengujian Mutu (Automated QA Testing)

AgroOrder GPA dilengkapi suite pengujian otomatis komprehensif:
```bash
php artisan test
```
**Hasil Pengujian:**
```text
Tests:    208 passed (478 assertions)
Duration: 10.51s
Status:   100% GREEN (Passed)
```

---

## 👥 7. Tim Pengembang

- **Siswa 1 (Frontend Specialist & UI/UX Designer):** Perancangan antarmuka responsif, layout cetak dokumen (A5 & Thermal), integrasi alur pemesanan, dan penyusunan panduan pengguna.
- **Siswa 2 (Backend Architect & Database Administrator):** Perancangan arsitektur database relasional 3NF, logika pemotongan stok otomatis, service layer, security hardening, dan manajemen unit testing.

*Koperasi Produsen Agro Green Pasundan &bull; SMK Budi Bakti &bull; 2026/2027*
