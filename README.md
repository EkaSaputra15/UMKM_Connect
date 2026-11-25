# UMKM Connect — Full-Stack Laravel 11 CRM & Loyalty Platform

Platform CRM & manajemen loyalitas multi-tenant untuk UMKM berbasis layanan (cuci sepatu & tas, barbershop & salon, laundry, bengkel, klinik kecantikan) dibangun sesuai **PRD UMKM Connect v1.0**.

## Tech Stack
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade Templates, Tailwind CSS, Livewire 3, Vite, Lucide Icons, Chart.js
- **Database**: MySQL 8.0 + Eloquent ORM (Multi-Tenant `store_id` Global Scope)
- **Async & Email**: Laravel Mail, Database Queue Driver (`SendEmailReceiptJob`, `SendBroadcastEmailJob`), Task Scheduler (`broadcasts:process-scheduled`)

## Cara Menjalankan di Laragon (Lokal)
1. Pastikan Laragon menggunakan **PHP 8.3+** (`C:\laragon\bin\php\php-8.3.36-1-Win32-vs16-x64`) dan nyalakan **MySQL** di Laragon.
2. Buat database MySQL bernama `umkm_connect` (sesuai `.env`), lalu jalankan migrasi & seeder:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
3. Build aset frontend (atau jalankan dev server):
   ```bash
   npm run build
   ```
4. Jalankan queue worker & scheduler untuk pengiriman nota digital & broadcast email asinkron:
   ```bash
   php artisan queue:work
   php artisan schedule:work
   ```
5. Akses aplikasi melalui `php artisan serve` atau Virtual Host Laragon (`public/` sebagai document root).

## Akun Demo (Seeder)
- **Owner Toko 1 (CleanShoe & Care Studio)**: `owner@umkm.com` / `password`
- **Kasir Toko 1**: `kasir@umkm.com` / `password`
- **Owner Toko 2 (Pangkas Rapi Barbershop)**: `owner2@umkm.com` / `password`
