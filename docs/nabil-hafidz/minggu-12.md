# Catatan Mingguan — Minggu 12
**Nama:** Nabil Hafidz Mubarok  
**NIM:** 10241055  
**Kelompok:** A07  
**Peran:** Infrastruktur, Database & Supervisor  
**Topik:** Deployment Server Produksi: Konfigurasi Database MySQL (`kampuslms_prod`), Process Manager Supervisor, dan Pemrosesan Antrean Latar Belakang 24 Jam Nonstop  

---

*READ*

### 1. Pembedahan Arsitektur Basis Data Produksi vs Pengembangan
Pada tahap pengembangan lokal (Minggu 01–11), aplikasi KampusLMS berjalan di atas driver **SQLite** berbasis file tunggal (`database/database.sqlite`). Namun, untuk lingkungan **Production VPS**, arsitektur basis data harus ditingkatkan ke **MySQL Server 8.0** dengan pertimbangan:
- **Konkurensi & Row-Level Locking:** SQLite mengunci seluruh basis data (*database-level lock*) saat terjadi operasi penulisan (write), sehingga jika banyak mahasiswa mengumpulkan tugas atau worker memproses notifikasi secara bersamaan, akan terjadi bottleneck `database is locked`. MySQL mendukung *Row-Level Locking* lewat engine InnoDB.
- **Karakter Unicode Penuh:** Konfigurasi database produksi menggunakan `utf8mb4` dengan collation `utf8mb4_unicode_ci`, mendukung penyimpanan teks multibahasa, simbol matematika, dan emoji tanpa risiko *character corruption*.
- **Prinsip Least Privilege & Network Isolation:** Dibuat pengguna khusus (`kampuslms_user`) yang hanya diikat ke antarmuka loopback lokal (`@'localhost'`). Pengguna ini hanya diberikan izin DDL/DML terbatas pada database `kampuslms_prod` saja, tanpa hak administratif global (`*.*`, `SUPER`, `GRANT OPTION`), sehingga port 3306 tidak dapat dieksploitasi dari internet publik.

### 2. Arsitektur Supervisor & Pemrosesan Antrean Latar Belakang
Pemrosesan antrean latar belakang pada Laravel 12 dijalankan melalui perintah `php artisan queue:work database`. 
Di lingkungan server Linux, eksekusi perintah terminal biasa terikat pada sesi antarmuka virtual (*pseudo-terminal slave* / PTS). Jika pengguna menutup jendela terminal SSH, kernel Linux akan mengirimkan sinyal **SIGHUP** (*Signal Hang Up*) ke *session leader*, yang otomatis menghentikan seluruh *child process* di bawahnya.

Untuk menjamin worker berjalan **24 jam nonstop**, digunakan **Supervisor** sebagai process manager tingkat sistem:
- **Daemon Master (`supervisord`):** Berjalan sebagai daemon sistem latar belakang yang dimonitor oleh `systemd` (PID 1). `supervisord` bertindak sebagai *master process* yang meng-fork dan mengontrol *worker child process*.
- **Control Interface (`supervisorctl`):** Utilitas baris perintah untuk berinteraksi dengan daemon melalui UNIX Domain Socket (`/var/run/supervisor.sock`), memungkinkan aksi start, stop, restart, dan pemantauan status proses secara realtime.
- **Auto-Restart & Health Loop:** Jika worker mengalami *fatal error*, kehabisan memori, atau terhenti karena `queue:restart`, Supervisor langsung mendeteksi sinyal terminasi (*exit status code*) dan membangkitkan proses baru secara otomatis dalam hitungan milidetik (`autorestart=true`).
- **Isolasi Hak Akses (`user=www-data`):** Worker dieksekusi menggunakan akun non-root `www-data` demi mencegah potensi *privilege escalation* jika terdapat kerentanan eksekusi kode di dalam job antrean.

---

*BREAK*

### Eksperimen Kerusakan Sengaja (Tujuh Percobaan Infrastruktur & Queue)

| # | Yang dicoba | Yang harus diamati | Perilaku yang Terjadi | Penjelasan Teknis & Bahaya / Pelajaran yang Didapat |
|---|-------------|--------------------|-----------------------|-----------------------------------------------------|
| 1 | Jalankan `php artisan queue:work` langsung di sesi SSH, lalu tutup terminal SSH | **Apakah worker tetap hidup atau mati?** | Worker langsung terbunuh (*terminated*) seketika terminal SSH ditutup. Job baru yang masuk ke tabel `jobs` menumpuk tanpa pernah diproses, dan mahasiswa tidak menerima notifikasi. | Sinyal **SIGHUP** dikirim ke semua proses turunan sesi TTY/PTS saat koneksi terputus. Menjalankan worker secara manual tanpa daemon manager sangat berbahaya di production karena worker rentan mati saat koneksi jaringan admin putus. |
| 2 | Simulasikan worker crash mendadak menggunakan `kill -9 <PID_WORKER>` saat Supervisor aktif | **Apakah antrean macet atau pulih otomatis?** | Dalam waktu < 1 detik, Supervisor mendeteksi matinya proses worker, mencatatnya di log, lalu otomatis membuat PID baru (*respawn*). Status di `supervisorctl status` tetap **RUNNING**. | Fitur `autorestart=true` menjamin *high availability*. Tanpa Supervisor, worker yang mati akibat segfault, memory limit, atau exception fatal akan mati permanen tanpa ada yang menyalakan kembali. |
| 3 | Konfigurasi Supervisor dengan direktiva `user=root` | **Periksa file kepemilikan log dan cache** | File log dan cache yang di-generate oleh worker dimiliki oleh `root:root`. Saat web server Nginx (`www-data`) mencoba menulis ke file tersebut, terjadi error `Permission Denied` (HTTP 500). | Pelanggaran prinsip *Least Privilege*. Menjalankan service aplikasi sebagai `root` membuka celah eskalasi hak akses sistemik dan menyebabkan konflik *file permission* antar komponen web server. |
| 4 | Jalankan worker tanpa flag `--max-time` dan `--memory` dalam jangka panjang | **Pantau konsumsi RAM worker** | Konsumsi memori RAM worker perlahan naik (*memory leak*) seiring ribuan notifikasi yang diproses, hingga akhirnya VPS kehabisan memori (*Out Of Memory* / OOM Killer aktif). | Siklus hidup proses PHP CLI bersifat persisten. Objek statis, query log, dan library yang tidak dibersihkan oleh garbage collector akan terakumulasi. Parameter `--max-time=3600` memaksa worker me-recycle dirinya setiap 1 jam untuk me-reset memori. |
| 5 | Ganti password database di `.env` dengan password yang salah | **Amati log worker di `/var/log/supervisor/`** | Supervisor mengalami *rapid restart loop* (*BACKOFF* / *FATAL*) karena worker melempar `PDOException: Access denied for user`. File log terisi ratusan baris error koneksi. | Worker Laravel membutuhkan koneksi database yang valid sejak inisialisasi bootstrapper. Kegagalan autentikasi database langsung menghentikan bootstrap script sebelum queue polling dimulai. |
| 6 | Perbarui kode listener di repo, tetapi TIDAK menjalankan `php artisan queue:restart` | **Apakah logika kode baru langsung diterapkan?** | Worker tetap mengeksekusi logika kode lama yang tersimpan di memori RAM saat worker pertama kali di-boot (*stale code*). Perubahan kode baru tidak berdampak sama sekali pada notifikasi. | Worker `queue:work` memuat framework ke memori sekali di awal. Seluruh perubahan file PHP setelah worker aktif tidak akan dibaca sampai proses worker tersebut dimatikan dan dihidupkan ulang melalui `php artisan queue:restart`. |
| 7 | Hapus parameter `stopwaitsecs=3600` saat worker sedang memproses job pengiriman batch | **Amati status job saat service di-restart** | Supervisor hanya menunggu default 10 detik sebelum mengirim sinyal paksa `SIGKILL`. Job yang belum selesai dipotong di tengah jalan, meninggalkan data tidak konsisten atau status gantung di `jobs`. | Parameter `stopwaitsecs` memberikan jeda waktu aman (*graceful termination*) bagi worker untuk menuntaskan job aktif sebelum proses anak ditutup paksa. |

---

*FIX*

### Langkah Mitigasi & Hardening Konfigurasi

1. **Penerapan Konfigurasi Supervisor Standar Produksi:**
   Dibuat berkas `/etc/supervisor/conf.d/kampuslms-worker.conf` (salinan repo di [`deploy/supervisor/kampuslms-worker.conf`](../../deploy/supervisor/kampuslms-worker.conf)) dengan direktiva perlindungan menyeluruh:
   - `numprocs=2`: Dua worker paralel untuk throughput optimal.
   - `user=www-data`: Mencegah *privilege escalation*.
   - `--sleep=3`: Mencegah *CPU throttling* saat antrean kosong.
   - `--tries=3`: Mencegah job bermasalah mengalami infinite retry loop.
   - `--max-time=3600`: Pencegahan akumulasi *memory bloat*.
   - `stopasgroup=true` & `killasgroup=true`: Membasmi potensi *zombie child process*.

2. **Isolasi Database MySQL Produksi:**
   - Database dibuat dengan nama `kampuslms_prod` menggunakan karakter `utf8mb4`.
   - Dibuat pengguna khusus `kampuslms_user` yang hanya diizinkan mengakses dari host `localhost` (`127.0.0.1`).
   - Diberikan password kuat acak sepanjang 24 karakter dengan entropi tinggi (`openssl rand -base64 24`).
   - Hak akses dibatasi hanya untuk tabel `kampuslms_prod.*` tanpa hak administratif server.

---

*BUILD*

### Implementasi Berkas & Skrip Deliverable

Seluruh deliverable telah dibuat dan diintegrasikan ke repositori:

1. **Berkas Konfigurasi Supervisor:**
   - Lokasi Server: `/etc/supervisor/conf.d/kampuslms-worker.conf`
   - Berkas Repositori: [`deploy/supervisor/kampuslms-worker.conf`](../../deploy/supervisor/kampuslms-worker.conf)

2. **Skrip Konfigurasi Database MySQL Produksi:**
   - Skrip SQL: [`deploy/mysql/setup_production_db.sql`](../../deploy/mysql/setup_production_db.sql)
   - Skrip Bash Otomasi: [`scripts/setup-mysql-production.sh`](../../scripts/setup-mysql-production.sh)

3. **Skrip Otomasi Deployment Supervisor di Server:**
   - Berkas: [`scripts/setup-supervisor.sh`](../../scripts/setup-supervisor.sh)

4. **Skrip Uji Pemrosesan Antrean saat Sesi SSH Ditutup:**
   - Berkas: [`scripts/test-queue-ssh-closed.sh`](../../scripts/test-queue-ssh-closed.sh)

---

*CHECKPOINT*

### 1. Bukti Status Supervisor Worker di VPS (Tangkapan Layar Terminal)

```text
root@vps-kampuslms:~# supervisorctl status
kampuslms-worker:kampuslms-worker_00   RUNNING   pid 14285, uptime 03:42:15
kampuslms-worker:kampuslms-worker_01   RUNNING   pid 14286, uptime 03:42:15
```

### 2. Bukti Independensi Proses dari Sesi SSH (Process Hierarchy Tree)

```text
root@vps-kampuslms:~# ps -ef | grep queue:work
UID          PID    PPID  C STIME TTY          TIME CMD
www-data   14285    1210  0 10:00 ?        00:00:12 php /var/www/kampuslms/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=90
www-data   14286    1210  0 10:00 ?        00:00:11 php /var/www/kampuslms/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=90
```
> **Catatan Analisis:** Kolom `PPID` adalah **1210** (daemon `supervisord`), dan kolom `TTY` bernilai **`?`** (bukan `pts/0`). Ini membuktikan bahwa proses worker telah ter-daemonisasi secara penuh dan **tidak terikat pada sesi terminal pengguna**.

### 3. Log Pengujian Pemrosesan Notifikasi Tugas (SSH Ditutup):

```text
[2026-11-09 10:15:02] Processing: App\Listeners\SendAssignmentPublishedNotification
[2026-11-09 10:15:03] Processed:  App\Listeners\SendAssignmentPublishedNotification
[2026-11-09 10:15:03] Processing: App\Notifications\NewAssignmentNotification
[2026-11-09 10:15:03] Processed:  App\Notifications\NewAssignmentNotification
```
*Hasil:* Saat sesi SSH ditutup paksa, tugas baru yang dipublikasikan oleh dosen tetap berhasil mendistribusikan notifikasi ke seluruh mahasiswa secara otomatis di latar belakang.

---

### 4. Penguasaan Materi Wawancara Dosen (Fokus Wawancara Nabil)

#### Q1: "Jelaskan arsitektur Supervisor dan bagaimana ia mengelola worker Laravel di server produksi!"
> *"Izin menjawab Pak/Bu: Arsitektur Supervisor di server kami menggunakan pola **Master-Child Process Management**:*
> 1. ***Master Daemon (`supervisord`):*** *Berjalan sebagai daemon sistem latar belakang di bawah `systemd` (PID 1). Bertugas membaca konfigurasi `/etc/supervisor/conf.d/kampuslms-worker.conf` dan melakukan `fork()`/`exec()` untuk membangkitkan subproses worker.*
> 2. ***Child Process (Queue Workers):*** *Kami mengonfigurasi `numprocs=2` di bawah user `www-data` yang melakukan polling ke tabel `jobs` secara asinkron.*
> 3. ***Auto-Restart Loop:*** *Supervisor memonitor PID worker via sinyal UNIX. Jika worker terhenti (baik karena `queue:restart` pasca deploy, crash, atau memory limit), Supervisor otomatis membangkitkan proses baru dalam hitungan milidetik (`autorestart=true`).*
> 4. ***Control Plane (`supervisorctl`):*** *Utilitas CLI untuk mengontrol daemon melalui UNIX domain socket (`/var/run/supervisor.sock`)."*

#### Q2: "Apa akibat fatalnya jika background worker mati diam-diam (*silent failure*) di server produksi?"
> *"Jika worker mati diam-diam tanpa Supervisor, ada 4 dampak fatal:*
> 1. ***Queue Backlog Explosion:*** *Aplikasi web tetap menerima request dan memasukkan job ke tabel `jobs` tanpa error HTTP di browser, namun antrean terus menumpuk dan tidak pernah diproses.*
> 2. ***Kegagalan Notifikasi Akademik:*** *Ketika dosen merilis tugas atau deadline, mahasiswa tidak menerima notifikasi sehingga berisiko terlambat submit.*
> 3. ***Ketidaksesuaian State Nilai:*** *Ketika dosen menilai tugas, notifikasi tidak sampai ke mahasiswa dan memicu komplain.*
> 4. ***Silent Erosion of Trust:*** *Karena browser menampilkan 200 OK, pengguna mengira sistem normal padahal fitur otomasi latar belakang lumpuh total."*

#### Q3: "Mengapa worker harus di-restart (`php artisan queue:restart`) setiap kali deploy kode baru?"
> *"Worker `queue:work` memuat seluruh kode framework, model, dan listener ke dalam RAM **hanya sekali di awal** (long-lived process). Jika ada perubahan kode di listener/notifikasi, worker yang sedang aktif di RAM tidak akan membaca kode baru tersebut (*stale code*). Dengan `queue:restart`, Laravel mengirim sinyal agar worker berhenti secara graceful setelah job aktif selesai, lalu Supervisor langsung menyalakan worker baru yang memuat kode terbaru dari repositori."*
