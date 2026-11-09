# Catatan Individu Minggu - 12 - Patra Ananda (10241061)

**Nama:** Patra Ananda  
**NIM:** 10241061  
**Kelompok:** A07  
**Fokus Tugas:** Penyiapan `Dockerfile` Produksi, Skrip `deploy.sh` Otomatis & Integrasi Deployment  
**Topik:** Deployment ke Server Produksi, Containerisasi Docker, dan Keamanan Server  

---

## 12.1 Konsep Deployment Produksi & Security Hardening

Pada tahap ini, aplikasi KampusLMS beralih dari lingkungan lokal (*development*) menuju server publik (*production*). Perubahan ini menuntut pemahaman arsitektur yang ketat:

| Komponen | Lingkungan Lokal | Lingkungan Produksi | Penjelasan & Alasan Keamanan |
|---|---|---|---|
| **`APP_ENV`** | `local` | `production` | Mengaktifkan pengamanan otomatis framework (misal memblokir `migrate:fresh`). |
| **`APP_DEBUG`** | `true` | **`false`** | **Aturan Mutlak:** Mencegah kebocoran kredensial database & `APP_KEY` saat terjadi crash. |
| **Aset Frontend** | `npm run dev` | `npm run build` | Mengompilasi CSS/JS menjadi aset statis di `public/build/` tanpa menjalankan server dev Vite. |
| **Penyimpanan Cache** | Mati (Dinamis) | `config/route/view:cache` | Menyatukan ratusan file ke dalam satu file memori statis untuk kecepatan 2-3x lipat. |
| **Composer** | Paket dev aktif | `--no-dev --optimize-autoloader` | Membuang library dev (Debugbar, Faker) dan mengindeks class PHP ke memori. |
| **Queue Worker** | Manual via terminal | Managed background worker | Menjamin antrean notifikasi diproses secara terpisah tanpa membebani request web utama. |
| **Protokol Web** | `http://` | **`https://` (SSL Wajib)** | Enkripsi end-to-end; mencegah sniffing kredensial login di jaringan publik. |
| **Penanganan Error** | Tampil di layar | Tercatat di file log | Pengunjung hanya melihat layar 500 generik; detail teknis dilacak via `storage/logs/laravel.log`. |

---

## 12.3 Read, Break, Fix, Build

### READ: Bedah Konfigurasi Server Produksi

#### 1. Menentukan Document Root Web Server
* **Konfigurasi yang Benar:** `DocumentRoot /var/www/html/public` (Apache) atau `root /var/www/kampuslms/public;` (Nginx).
* **Analisis Keamanan:** Web server hanya boleh membuka folder `public/`. Jika diarahkan ke root proyek, penyerang dapat langsung mengunduh file `.env`, `composer.json`, dan file log server via browser.

#### 2. Perbedaan `php artisan about`
* Pada server produksi, flag **Environment** bernilai `production`, **Debug Mode** bernilai `DISABLED`, **Config & Route** bernilai `CACHED`, dan database terhubung ke `MySQL 8.0` (bukan SQLite lokal).

#### 3. Verifikasi Tiga Kunci Anti-Bocor (`curl -I`)
Dijalankan terhadap server live:
```bash
curl -I https://domain/.env                     # Hasil: 404 Not Found (AMAN)
curl -I https://domain/storage/logs/laravel.log # Hasil: 404 Not Found (AMAN)
curl -I https://domain/composer.json            # Hasil: 404 Not Found (AMAN)
```
Status **`404 Not Found`** membuktikan bahwa server menolak menyajikan file di luar folder `public/`.

---

### BREAK: Analisis Eksperimen Kerusakan (8 Skenario)

<!-- Bagian BREAK akan diisi mandiri oleh Patra -->

---

### FIX: Perbaikan 7 Cacat pada Branch `w12` (`kampuslms-broken`)

Sebagai Lead Dev, saya menganalisis 7 bug pada branch `w12`:
1. **`.env.example` Bocor:** Mengganti kredensial database riil dengan placeholder dummy (`your_password_here`).
2. **`.gitignore` Tidak Lengkap:** Menambahkan `.env` agar file konfigurasi lokal tidak terdorong ke GitHub.
3. **`APP_DEBUG=true` Default:** Mengubah nilai default di `.env.example` menjadi `false`.
4. **Salah Document Root:** Memperbaiki direktiva root dari root direktori proyek menjadi subfolder `public/`.
5. **Skrip `deploy.sh` Tanpa `set -e`:** Menambahkan `set -e` agar proses deployment berhenti seketika saat ada langkah yang gagal.
6. **Eksekusi `db:seed` Berulang:** Menghapus `php artisan db:seed` dari skrip deploy reguler untuk mencegah duplikasi/kerusakan data produksi.
7. **Tidak Ada `queue:restart`:** Menambahkan perintah restart worker agar daemon memuat kode baru ke memori RAM.

---

### BUILD: Pembuatan `Dockerfile` Produksi & Skrip Otomatisasi `deploy.sh` (Kontribusi Patra)

#### 1. Berkas [`Dockerfile`](../../Dockerfile) untuk Cloud Platform (Railway.app)
Sesuai panduan resmi asisten dosen, saya menyusun konfigurasi container berbasis `php:8.4-apache` dengan optimasi:
- **Pembersihan Modul MPM Ganda:** Menghapus `mpm_event` dan `mpm_worker` agar tidak bentrok dengan `mpm_prefork` (`AH00534: More than one MPM loaded`).
- **Isolasi Document Root:** Mengarahkan `APACHE_DOCUMENT_ROOT` ke `/var/www/html/public`.
- **Dukungan Port Dinamis Railway:** Skrip startup membaca environment variable `$PORT` dan menyelaraskan `ports.conf` serta VirtualHost Apache secara otomatis.
- **Auto Migration & Seeding:** Menjalankan migrasi dan seeding data demo otomatis saat container pertama kali aktif.

```dockerfile
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 8080
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* && export PORT=\"${PORT:-8080}\" && sed -i \"s/Listen .*/Listen $PORT/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost \\*:$PORT>/\" /etc/apache2/sites-available/000-default.conf && php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan migrate --force || true) && (php artisan db:seed --force || true) && exec apache2-foreground"]
```

#### 2. Skrip Otomasi [`deploy.sh`](../../deploy.sh)
Untuk pemeliharaan server berkelanjutan, saya menyusun skrip dengan fitur Fail-Fast (`set -e`), maintenance mode, pembaruan cache, restart worker, dan verifikasi healthcheck mandiri.

---

## 12.4 Persiapan Wawancara Checkpoint (Fokus Pengujian Patra)

### Q1: "Jelaskan peran baris `set -e` pada skrip `deploy.sh`!"
> *"Baris `set -e` menerapkan prinsip **Fail-Fast**, Pak. Jika terjadi kesalahan pada satu baris (misalnya `git pull` conflict atau `composer install` gagal), skrip langsung berhenti seketika. Tanpa `set -e`, Bash akan nekat melanjutkan ke langkah berikutnya, menjalankan migrasi atau menyalakan web dalam kondisi kode setengah jadi yang korup."*

### Q2: "Mengapa konfigurasi Apache di Dockerfile mewajibkan penghapusan `mpm_event` dan `mpm_worker`?"
> *"Apache hanya mengizinkan satu modul Multi-Processing Module (MPM) yang aktif pada saat bersamaan. Pada image Debian terbaru, paket sistem dapat mengaktifkan `mpm_event` berdampingan dengan `mpm_prefork`, yang menyebabkan Apache crash saat startup dengan error `AH00534: More than one MPM loaded`. Perintah pembersihan memastikan hanya `mpm_prefork` yang dimuat."*

### Q3: "Mengapa ada tahapan Health Check mandiri sebelum perintah `php artisan up`?"
> *"Health Check memastikan bahwa aplikasi benar-benar mengembalikan status HTTP 200/302 pada endpoint lokal sebelum web dibuka ke publik. Jika terjadi kesalahan fatal (misal koneksi database putus atau class hilang), skrip tidak akan menjalankan `php artisan up`, melainkan menahan sistem tetap dalam maintenance mode agar pengunjung tidak disambut oleh layar error 500."*
