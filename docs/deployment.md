# Laporan Deployment Server Produksi (Milestone M4)
**Proyek:** KampusLMS — Kelompok A07  
**Mata Kuliah:** SI2514024 - Pemrograman Web (Semester 5)  
**Tautan Aplikasi Live:** `https://kampuslms-production-a07.up.railway.app` *(domain aktif Railway)*  

---

## 1. Anggota Kelompok & Pembagian Tugas

| Nama | NIM | Peran | Tugas Utama |
|---|---|---|---|
| **Patra Ananda** | 10241061 | Lead Deployment & Docker | Buat file `Dockerfile`, skrip `deploy.sh`, dan pegang alur deploy ke Railway |
| **Nabil Hafidz Mubarok** | 10241055 | Database & Antrean Worker | Setup MySQL Railway, atur koneksi database internal, dan cek antrean notifikasi |
| **Nur Diah Indah Claryza** | 10241059 | Server & Keamanan | Atur Document Root `/public`, amankan file rahasia (`.env`), dan tes anti-bocor |
| **Nova Kartika Candra** | 10241057 | QA & Dokumentasi | Pasang domain HTTPS Railway, rapikan laporan deployment, dan uji tampilan web di HP |

---

## 2. Arsitektur Deployment & Spesifikasi Lingkungan Produksi

Kelompok A07 menerapkan arsitektur container cloud berbasis **Railway.app** sesuai dengan panduan resmi asisten dosen ([`PANDUAN_DEPLOY_RAILWAY.md`](https://github.com/ZakiZaidan/kampuslms/blob/main/docs/PANDUAN_DEPLOY_RAILWAY.md)):

* **Platform Hosting:** Railway Cloud Platform (PaaS)
* **Base Image Container:** `php:8.4-apache` (Debian-based)
* **Web Server:** Apache 2.4 dengan modul `mpm_prefork` dan `mod_rewrite` aktif
* **Document Root:** `/var/www/html/public` (terisolasi ketat, folder sistem dan `.env` berada di luar root web)
* **Bahasa & Runtime:** PHP 8.4 (ekstensi: `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`)
* **Basis Data:** Managed MySQL 8.0 bawaan Railway (Private Network via `${{MySQL.MYSQLHOST}}`)
* **Pengelola Paket:** Composer 2.x & NPM / Vite
* **Port Layanan:** Port dinamis Railway (didengarkan melalui konfigurasi `$PORT`, default internal `8080`)
* **Sertifikat Keamanan:** SSL/TLS otomatis via Railway Edge Proxy (HTTPS aktif)

---

## 3. Langkah Deployment Produksi di Railway.app

### Langkah 1: Penyiapan Berkas `Dockerfile` Siap Produksi
Berkas `Dockerfile` diletakkan pada akar direktori (*root*) proyek dengan konfigurasi bebas konflik MPM dan port dinamis:

```dockerfile
FROM php:8.4-apache

# 1. Install ekstensi sistem & library PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Konfigurasi Apache: aktifkan mod_rewrite, pastikan hanya mpm_prefork yang aktif (hindari error More than one MPM loaded)
RUN a2enmod rewrite \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 3. Atur Document Root ke /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Install Composer resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Salin seluruh kode proyek ke container
WORKDIR /var/www/html
COPY . .

# 6. Install dependensi composer untuk mode produksi
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 7. Berikan hak akses ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 8080

# 8. Script startup: atur PORT dinamis Railway, bersihkan cache, migrasi & seed, lalu jalankan Apache
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* && export PORT=\"${PORT:-8080}\" && sed -i \"s/Listen .*/Listen $PORT/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost \\*:$PORT>/\" /etc/apache2/sites-available/000-default.conf && php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan migrate --force || true) && (php artisan db:seed --force || true) && exec apache2-foreground"]
```

### Langkah 2: Pembuatan Project Baru di Railway
1. Masuk ke dashboard [Railway.app](https://railway.app) menggunakan akun GitHub.
2. Klik tombol **"New Project"** (atau **"+ Create a New Project"**).

### Langkah 3: Penambahan Database MySQL (1-Click)
1. Pilih **"Provision MySQL"** pada kanvas proyek.
2. Railway otomatis menyiapkan database MySQL terisolasi dalam hitungan detik dengan status **Online**.
3. Seluruh tabel akan dibuat otomatis oleh Laravel saat inisialisasi container pertama kali.

### Langkah 4: Menghubungkan Repositori GitHub
1. Klik tombol **"+ Create"** $\rightarrow$ pilih **"GitHub Repo"**.
2. Pilih repositori `kampuslms-kelompok-A07`.
3. Pastikan branch yang terhubung adalah `main`.
4. Klik **"Deploy Now"** dan pastikan opsi **"Toggle Auto Deploy"** menyala.

### Langkah 5: Konfigurasi Environment Variables di Railway
Pada service aplikasi KampusLMS, buka tab **"Variables"** $\rightarrow$ klik **"Raw Editor"**, dan masukkan konfigurasi berikut:

```env
APP_NAME=KampusLMS
APP_ENV=production
APP_KEY=base64:yXMLWauESwOYJgA6SgjpZiDSVcnEruvAAY4m1+OW0Hk=
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

> **Catatan Keamanan Private Network:** Penulisan variabel `${{MySQL.MYSQLHOST}}` memanfaatkan jaringan internal Railway. Akses port database tidak terekspos ke internet publik, bebas biaya bandwidth antar-service, dan memiliki latensi minimal.

### Langkah 6: Pembuatan Domain Publik (HTTPS)
1. Buka tab **"Settings"** pada service KampusLMS.
2. Pada bagian **"Networking"** $\rightarrow$ **"Public Networking"**, klik tombol **"Generate Domain"**.
3. Jika diminta port aplikasi, masukkan **`8080`**.
4. Railway secara otomatis menerbitkan URL dengan format `*.up.railway.app` beserta sertifikat SSL aktif.

### Langkah 7: Verifikasi Migrasi & Seeding Otomatis
Perintah `(php artisan migrate --force || true) && (php artisan db:seed --force || true)` pada script startup container otomatis mengisi tabel-tabel utama:
- `users`: data admin, dosen, dan mahasiswa demo.
- `courses`: mata kuliah aktif.
- `assignments`, `materials`, `submissions`, dan `grades`.

---

## 4. Skrip Otomasi Deployment (`deploy.sh`)

Untuk deployment langsung pada server VPS atau pengujian lokal CI/CD, kelompok kami menyertakan skrip otomatisasi [`deploy.sh`](../deploy.sh) yang dirancang dengan prinsip **Fail-Fast**:

* **Fail-Fast (`set -e`):** Skrip langsung membatalkan eksekusi jika terjadi kesalahan pada salah satu perintah.
* **Maintenance Mode Aman:** Menampilkan halaman pemeliharaan kepada pengunjung selama pembaruan basis data berlangsung (`php artisan down`).
* **Pembersihan dan Pembaruan Cache:** Mengompilasi konfigurasi, rute, dan view untuk performa maksimal.
* **Worker Restart:** Menjalankan `php artisan queue:restart` agar daemon memuat kode terbaru ke memori RAM.
* **Pemeriksaan Kesehatan Mandiri (Self-Healthcheck):** Memeriksa respons HTTP (200/302) pada endpoint lokal sebelum sistem dibuka kembali ke publik (`php artisan up`).

---

## 5. Audit Keamanan Pasca-Deployment (Bukti Uji Anti-Bocor)

Pengujian dilakukan dari luar jaringan server terhadap URL produksi untuk membuktikan bahwa perlindungan server berjalan efektif:

```bash
# 1. Uji Proteksi Berkas .env (Wajib 404 / 403)
curl -I https://kampuslms-production-a07.up.railway.app/.env
# HTTP/2 404 Not Found  --> LOLOS (Berkas sensitif tidak dapat diakses)

# 2. Uji Proteksi Log Internal (Wajib 404)
curl -I https://kampuslms-production-a07.up.railway.app/storage/logs/laravel.log
# HTTP/2 404 Not Found  --> LOLOS (Log sistem terlindungi)

# 3. Uji Proteksi Composer (Wajib 404)
curl -I https://kampuslms-production-a07.up.railway.app/composer.json
# HTTP/2 404 Not Found  --> LOLOS (Daftar dependensi tidak bocor)

# 4. Uji Pengalihan HTTP ke HTTPS (Wajib 301 / 308)
curl -I http://kampuslms-production-a07.up.railway.app/
# HTTP/1.1 301 Moved Permanently
# Location: https://kampuslms-production-a07.up.railway.app/  --> LOLOS (Enkripsi SSL aktif)
```

---

## 6. Rangkuman Troubleshooting & Solusi Kendala Nyata

Berdasarkan pengalaman pengujian dan panduan asisten, berikut adalah daftar kendala yang berhasil ditangani:

1. **Konflik Modul Apache (`More than one MPM loaded`):**
   * *Gejala:* Container crash dengan pesan `AH00534: Configuration error: More than one MPM loaded`.
   * *Solusi:* Memastikan modul `mpm_event` dan `mpm_worker` dihapus dan hanya `mpm_prefork` yang diaktifkan baik pada tahap build image maupun pada perintah `CMD`.
2. **Kesesuaian Versi PHP 8.4:**
   * *Gejala:* `composer install` gagal karena dependensi Symfony membutuhkan PHP $\ge$ 8.4.1.
   * *Solusi:* Menggunakan image dasar `php:8.4-apache` dan menyertakan opsi `--ignore-platform-reqs` pada Composer.
3. **Konflik Nilai Enum Status Mata Kuliah:**
   * *Gejala:* Terjadi error `SQLSTATE[01000]: 1265 Data truncated for column 'status'` saat seeder memasukkan status yang tidak terdaftar (`published`).
   * *Solusi:* Memastikan seluruh seeder menggunakan status `'active'` sesuai definisi skema migrasi tabel `courses`.
4. **Pencegahan Error 502 Bad Gateway:**
   * *Gejala:* Railway menampilkan pesan *Application failed to respond*.
   * *Solusi:* Memastikan konfigurasi port networking pada dashboard Railway mengarah ke port **`8080`** sesuai variabel port di `Dockerfile`.
5. **Aset Frontend Vite Tidak Terbaca:**
   * *Gejala:* Tampilan web polos tanpa styling CSS Tailwind.
   * *Solusi:* Mengompilasi aset melalui `npm run build` sebelum file dipaketkan atau memastikan artefak build di direktori `public/build/` tersedia dan terbaca oleh Apache.

---

## 7. Verifikasi Akun Pengujian Demo

Sistem siap diuji oleh dosen pengampu dan asisten dengan akun demo berikut:
* **Admin:** `admin@kampuslms.test` / password: `password`
* **Dosen:** `dosen@kampuslms.test` / password: `password`
* **Mahasiswa:** `mahasiswa@kampuslms.test` / password: `password`
