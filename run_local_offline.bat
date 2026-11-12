@echo off
title PELUNCUR SERVER DARURAT OFFLINE UKK RPL - AGROORDER GPA
color 0A
echo ==============================================================
echo   MEMULAI SERVER LOKAL AGROORDER GPA (KONTINJENSI OFFLINE LAN)
echo   Koperasi Produsen Agro Green Pasundan - UKK RPL 2026/2027
echo ==============================================================
echo.
echo Pastikan MySQL (Laragon / XAMPP) telah aktif pada port 3306!
echo Mendeteksi alamat IP lokal laptop ini...
for /f "tokens=4" %%a in ('route print ^| findstr 0.0.0.0.*0.0.0.0') do set IP=%%a
echo Alamat IP Server Anda di Jaringan: http://%IP%:8000
echo.
echo [1/3] Membersihkan dan Mengoptimalkan Cache Sistem...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear
echo.
echo [2/3] Memastikan Akses Berkas Upload (Storage Symlink)...
call php artisan storage:link
echo.
echo [3/3] Menjalankan Server Web Terbuka ke Seluruh Jaringan (0.0.0.0)...
echo --------------------------------------------------------------
echo - Akses dari Laptop Sendiri : http://127.0.0.1:8000
echo - Akses dari Laptop Asesor  : http://%IP%:8000
echo --------------------------------------------------------------
echo Tekan tombol CTRL + C jika ingin menghentikan server.
echo ==============================================================
call php artisan serve --host 0.0.0.0 --port 8000
pause

