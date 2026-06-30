# SOP: Deploy Aplikasi ke Production

## Tujuan

Menstandarkan langkah deploy agar setiap release berjalan konsisten dan dapat diaudit.

## Use case

Digunakan saat:

- Release versi baru ke production
- Hotfix kritis yang perlu segera di-deploy
- Rollout setelah QA sign-off di staging

## Requirements

- Akses SSH ke server production (user deploy)
- Git tag release sudah dibuat
- Backup database terbaru tersedia
- Maintenance window disetujui (jika diperlukan)

## Langkah-langkah

1. Login ke server production:

```bash
ssh deploy@prod.example.com
```

2. Masuk ke direktori aplikasi dan pull tag release:

```bash
cd /var/www/app
git fetch --tags
git checkout v1.2.3
```

3. Install dependensi dan jalankan migrasi:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Reload service:

```bash
sudo systemctl reload php-fpm
sudo systemctl reload nginx
```

## Validasi

- Buka `https://app.example.com/health` — harus HTTP 200
- Cek log aplikasi: tidak ada error baru dalam 5 menit pertama
- Smoke test: login, buka dashboard, satu alur kritis

## Rollback

Jika deploy gagal:

```bash
git checkout v1.2.2
composer install --no-dev --optimize-autoloader
php artisan migrate --force
sudo systemctl reload php-fpm nginx
```

Restore database dari backup jika migrasi tidak reversible.

## Catatan

- Deploy di luar jam sibuk (mis. 22:00–06:00 WITA)
- Catat versi dan waktu deploy di channel tim #deployments
