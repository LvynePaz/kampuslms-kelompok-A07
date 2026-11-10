# Laporan Deployment Server Produksi (Milestone M4)

**Proyek:** KampusLMS — Kelompok A07  
**Mata Kuliah:** SI2514024 - Pemrograman Web (Semester 5)  
**Tautan Aplikasi Live:** [KampusLMS](https://kampuslms-kelompok-a07-production.up.railway.app)

---

## 1. Anggota Kelompok & Pembagian Tugas

| Nama | NIM | Peran | Tugas Utama |
|---|---|---|---|
| **Patra Ananda** | 10241061 | Lead Deployment & Docker | Membuat file `Dockerfile`, skrip `deploy.sh`, dan menangani proses deployment ke Railway. |
| **Nabil Hafidz Mubarok** | 10241055 | Database & Antrean Worker | Menyiapkan MySQL Railway, mengatur koneksi database internal, dan memeriksa antrean notifikasi. |
| **Nur Diah Indah Claryza** | 10241059 | Server & Keamanan | Mengatur Document Root `/public`, mengamankan file rahasia seperti `.env`, dan melakukan pengujian keamanan. |
| **Nova Kartika Candra** | 10241057 | QA & Dokumentasi | Menguji fungsi aplikasi berdasarkan role, memeriksa tampilan mobile, menguji pengunduhan materi, dan melengkapi dokumentasi hasil pengujian. |

---

## 2. Arsitektur Deployment & Spesifikasi Lingkungan Produksi

Kelompok A07 menggunakan arsitektur container cloud berbasis **Railway.app** sesuai dengan panduan asisten dosen ([PANDUAN_DEPLOY_RAILWAY.md](https://github.com/ZakiZaidan/kampuslms/blob/main/docs/PANDUAN_DEPLOY_RAILWAY.md)).

Spesifikasi lingkungan yang digunakan berdasarkan konfigurasi repository dan dokumentasi deployment kelompok adalah sebagai berikut:

- **Platform Hosting:** Railway Cloud Platform (PaaS).
- **Base Image Container:** `php:8.4-apache` berbasis Debian.
- **Web Server:** Apache 2.4 dengan modul `mpm_prefork` dan `mod_rewrite` aktif.
- **Document Root:** `/var/www/html/public`, sehingga file aplikasi dan konfigurasi di luar folder `public` tidak menjadi bagian dari document root.
- **Bahasa & Runtime:** PHP 8.4 dengan ekstensi `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, dan `zip`.
- **Basis Data:** MySQL melalui layanan Railway dengan koneksi internal menggunakan variable reference seperti `${{MySQL.MYSQLHOST}}`.
- **Pengelola Paket:** Composer dan NPM/Vite.
- **Port Layanan:** Menggunakan port dinamis dari Railway melalui variabel `$PORT`, dengan nilai default `8080` pada Dockerfile.
- **Sertifikat Keamanan:** Domain aplikasi menggunakan HTTPS melalui layanan Railway.

Informasi seperti versi database yang aktif, spesifikasi CPU/RAM, konfigurasi volume, dan pengaturan layanan pada dashboard Railway mengikuti konfigurasi yang digunakan oleh pengelola deployment.

---

## 3. Langkah Deployment Produksi di Railway.app

### Langkah 1: Penyiapan Berkas Dockerfile

Berkas `Dockerfile` diletakkan pada direktori utama (*root*) proyek. Konfigurasi ini digunakan untuk menyiapkan lingkungan PHP, Apache, dependency aplikasi, serta pengaturan port yang digunakan Railway.

Berikut konfigurasi Dockerfile yang digunakan:

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

# 2. Konfigurasi Apache
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

# 6. Install dependensi Composer untuk mode produksi
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 7. Berikan hak akses ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 8080

# 8. Startup: atur PORT, bersihkan cache, coba migrasi dan seeder, lalu jalankan Apache
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* && export PORT=\"${PORT:-8080}\"&& sed -i \"s/Listen .*/Listen $PORT/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost \\*:$PORT>/\" /etc/apache2/sites-available/000-default.conf && php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan migrate --force || true) && (php artisan db:seed --force || true) && exec apache2-foreground"]
```

Pada proses startup, aplikasi membersihkan cache konfigurasi, route, dan view. Setelah itu, container mencoba menjalankan migrasi database dan seeder sebelum Apache dijalankan. Kedua perintah tersebut menggunakan `|| true`, sehingga container tetap melanjutkan proses startup meskipun migrasi atau seeder mengalami kegagalan. Oleh karena itu, hasil eksekusinya tetap perlu diperiksa melalui deployment logs Railway.

### Langkah 2: Pembuatan Project Baru di Railway

1. Masuk ke dashboard [Railway.app](https://railway.app) menggunakan akun GitHub.
2. Pilih **New Project** atau **Create a New Project**.
3. Siapkan project yang akan digunakan untuk menjalankan aplikasi KampusLMS.

### Langkah 3: Penambahan Database MySQL

1. Pilih layanan **Provision MySQL** pada project Railway.
2. Railway menyediakan layanan database yang dapat dihubungkan dengan aplikasi.
3. Database digunakan untuk menyimpan data aplikasi seperti pengguna, mata kuliah, tugas, materi, submission, dan nilai.
4. Tabel aplikasi dibuat melalui proses migrasi Laravel.

### Langkah 4: Menghubungkan Repository GitHub

1. Pilih opsi untuk menambahkan repository GitHub pada project Railway.
2. Pilih repository `kampuslms-kelompok-A07`.
3. Tentukan branch yang digunakan untuk deployment.
4. Aktifkan Auto Deploy jika proses deployment otomatis digunakan.

### Langkah 5: Konfigurasi Environment Variables di Railway

Pada service aplikasi KampusLMS, konfigurasi environment variables dilakukan melalui tab **Variables** pada dashboard Railway.

Contoh nama variabel yang digunakan:

```env
APP_NAME=KampusLMS
APP_ENV=production
APP_KEY=<diatur secara privat di Railway>
APP_DEBUG=false
APP_URL=https://kampuslms-kelompok-a07-production.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=<diatur secara privat di Railway>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

Nilai `APP_KEY` dan `DB_PASSWORD` merupakan informasi rahasia sehingga tidak dicantumkan dalam repository atau dokumentasi.

**Catatan Private Network:** Variable reference seperti `${{MySQL.MYSQLHOST}}` digunakan untuk menghubungkan aplikasi dengan layanan database Railway melalui konfigurasi internal antarlayanan.

### Langkah 6: Pembuatan Domain Publik (HTTPS)

1. Buka pengaturan service aplikasi pada dashboard Railway.
2. Masuk ke bagian **Settings** dan **Networking**.
3. Atur domain publik yang akan digunakan oleh aplikasi.
4. Pastikan port layanan sesuai dengan konfigurasi aplikasi pada Dockerfile.
5. Gunakan URL HTTPS untuk mengakses aplikasi.

URL produksi KampusLMS yang digunakan kelompok:

https://kampuslms-kelompok-a07-production.up.railway.app

### Langkah 7: Verifikasi Migrasi dan Data Demo

Dockerfile menjalankan dua perintah berikut saat container dimulai:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Perintah migrasi digunakan untuk menerapkan perubahan struktur database berdasarkan file migrasi Laravel. Sementara itu, perintah seeder digunakan untuk menjalankan proses pengisian data awal aplikasi.

Kedua perintah menggunakan `|| true`, sehingga kegagalannya tidak selalu menghentikan proses startup container. Oleh karena itu, keberhasilan migrasi dan seeding perlu diperiksa melalui deployment logs Railway.

---

## 4. Skrip Otomasi Deployment (`deploy.sh`)

Repository juga menyediakan skrip otomatisasi [deploy.sh](../deploy.sh). Skrip ini merupakan bagian dari alat bantu deployment yang tersedia di repository dan bukan perintah startup yang dijalankan langsung oleh Dockerfile Railway.

Beberapa fungsi yang terdapat dalam skrip tersebut antara lain:

- **Fail-Fast (`set -e`):** Menghentikan proses apabila terjadi kesalahan pada salah satu perintah.
- **Maintenance Mode:** Menampilkan halaman pemeliharaan selama proses pembaruan aplikasi.
- **Pembersihan dan Pembaruan Cache:** Mengompilasi konfigurasi, route, dan view untuk membantu meningkatkan performa aplikasi.
- **Worker Restart:** Menjalankan `php artisan queue:restart` agar worker memuat kode terbaru.
- **Pemeriksaan Kesehatan Mandiri (Self-Healthcheck):** Memeriksa respons HTTP pada endpoint lokal sebelum aplikasi dibuka kembali.

---

## 5. Audit Keamanan Pasca-Deployment (Pengujian Anti-Bocor)

Pengujian keamanan dilakukan untuk memeriksa apakah file sensitif dan konfigurasi internal aplikasi dapat diakses secara langsung dari luar server.

Beberapa alamat yang digunakan untuk pengujian adalah:

```bash
# 1. Uji proteksi file .env
curl -I https://kampuslms-kelompok-a07-production.up.railway.app/.env

# 2. Uji proteksi log internal
curl -I https://kampuslms-kelompok-a07-production.up.railway.app/storage/logs/laravel.log

# 3. Uji proteksi file Composer
curl -I https://kampuslms-kelompok-a07-production.up.railway.app/composer.json

# 4. Uji pengalihan HTTP ke HTTPS
curl -I http://kampuslms-kelompok-a07-production.up.railway.app/
```

Dokumentasi sebelumnya mencatat hasil respons berupa `404 Not Found` untuk file sensitif dan `301 Moved Permanently` untuk pengalihan HTTP ke HTTPS.

Hasil tersebut merupakan catatan pengujian sebelumnya dan perlu diuji ulang pada URL produksi yang digunakan saat ini sebelum dinyatakan sebagai hasil verifikasi terbaru.

---

## 6. Rangkuman Troubleshooting & Solusi Kendala

Berdasarkan catatan pengerjaan deployment kelompok, terdapat beberapa kendala yang pernah ditemukan dan ditangani.

1. **Konflik Modul Apache (`More than one MPM loaded`)**
   - Gejala: Container mengalami kegagalan dengan pesan `AH00534: Configuration error: More than one MPM loaded`.
   - Solusi: Menghapus modul `mpm_event` dan `mpm_worker`, kemudian mengaktifkan `mpm_prefork` agar Apache hanya menggunakan satu MPM.

2. **Kesesuaian Versi PHP 8.4**
   - Gejala: Proses `composer install` mengalami kendala karena beberapa dependency membutuhkan versi PHP tertentu.
   - Solusi: Menggunakan image dasar `php:8.4-apache` dan opsi `--ignore-platform-reqs` pada Composer. Opsi tersebut melewati pemeriksaan persyaratan platform sehingga kompatibilitas dependency tetap perlu diperhatikan.

3. **Konflik Nilai Enum Status Mata Kuliah**
   - Gejala: Terjadi error `SQLSTATE[01000]: 1265 Data truncated for column 'status'` ketika seeder menggunakan status yang tidak sesuai dengan skema database.
   - Solusi: Menyesuaikan status mata kuliah menjadi `active` sesuai definisi skema tabel `courses`.

4. **Railway Application Failed to Respond**
   - Gejala: Railway menampilkan pesan `Application failed to respond`.
   - Solusi: Memeriksa konfigurasi port Apache dan memastikan aplikasi mendengarkan port yang sesuai dengan variabel `$PORT` dari Railway.

5. **Aset Frontend Vite Tidak Terbaca**
   - Gejala: Tampilan web tidak memiliki styling CSS yang seharusnya.
   - Solusi: Menjalankan `npm run build` untuk menghasilkan aset frontend sebelum aplikasi dikemas ke dalam container, serta memastikan hasil build tersedia di direktori `public/build/`.

6. **Unduh Submission oleh Dosen Menghasilkan 404**
   - Gejala: Saat Dosen mencoba mengunduh file submission pada aplikasi produksi, muncul pesan `404 File tidak ditemukan`.
   - Pemeriksaan: Ditemukan kemungkinan ketidaksesuaian antara path file submission yang tersimpan di database dan file fisik pada penyimpanan.
   - Status: Masalah ini masih memerlukan pemeriksaan dan perbaikan lebih lanjut pada sisi deployment. Pengujian ulang perlu dilakukan setelah perbaikan diterapkan oleh anggota tim yang menangani deployment.

Catatan kendala di atas berasal dari dokumentasi dan proses pengerjaan sebelumnya. Hasil deployment terbaru tetap perlu diperiksa melalui Railway logs dan pengujian langsung pada aplikasi produksi.

---

## 7. Verifikasi Akun Pengujian Demo

Akun demo yang disediakan melalui seeder digunakan untuk kebutuhan pengujian lokal/testing. Akun tersebut terdiri dari:

- **Admin:** `admin@kampuslms.test`
- **Dosen:** `dosen@kampuslms.test`
- **Mahasiswa:** `mahasiswa@kampuslms.test`

Password tidak dicantumkan dalam dokumentasi.

Berdasarkan konfigurasi Dockerfile saat ini, perintah seeder dicoba secara otomatis ketika container dimulai. Namun, keberadaan dan keberhasilan pembuatan akun demo di Railway tetap perlu dikonfirmasi melalui deployment logs dan pengujian login pada aplikasi produksi.

---

## 8. Pengujian Quality Assurance (QA)

Pengujian dilakukan untuk memastikan fitur aplikasi berjalan sesuai dengan hak akses masing-masing pengguna serta tampilan aplikasi dapat digunakan melalui perangkat mobile.

### 8.1 Pengujian Fungsional

Pengujian dilakukan secara lokal (localhost) menggunakan akun Admin, Dosen, dan Mahasiswa.

- **Admin:** Login berhasil, dashboard dapat dibuka, serta halaman pengelolaan mata kuliah dan pengguna dapat diakses.
- **Dosen:** Login berhasil, dashboard, daftar dan detail mata kuliah, halaman materi, tugas, submission, serta rekap nilai dapat dibuka.
- **Mahasiswa:** Login berhasil, dashboard, daftar dan detail mata kuliah, halaman materi, tugas, serta halaman nilai dapat dibuka.
- **Pengunduhan Materi:** Mahasiswa berhasil mengunduh materi dan membuka file yang diunduh.
- **Impor dan Ekspor Nilai:** Fitur impor dan ekspor nilai telah dicoba dan dilaporkan berhasil.

### 8.2 Pengujian Tampilan Mobile

Pengujian dilakukan dengan memeriksa tampilan aplikasi melalui browser pada perangkat mobile. Pada pemeriksaan awal ditemukan bahwa beberapa bagian tampilan belum menyesuaikan ukuran layar dengan baik.

Perbaikan kemudian dilakukan pada navbar agar menu lebih mudah digunakan melalui layar HP. Setelah perubahan dilakukan, tampilan mobile diperiksa kembali melalui localhost dan hasilnya terlihat lebih rapi.

Pengujian ulang melalui URL produksi masih diperlukan untuk memastikan perubahan sudah diterapkan dengan benar.

### 8.3 Catatan Pengujian Produksi

Pada pengujian sebelumnya di Railway, fitur unduh submission oleh Dosen pernah menampilkan pesan `404 File tidak ditemukan`.

Pemeriksaan menunjukkan adanya kemungkinan ketidaksesuaian antara data submission dan file yang tersimpan. Masalah ini masih perlu ditindaklanjuti oleh anggota tim yang menangani deployment.

Pengujian ulang pada aplikasi produksi diperlukan untuk memastikan file submission dapat diunduh dengan benar, termasuk setelah container mengalami restart atau redeployment.
