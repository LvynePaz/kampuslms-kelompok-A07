# Catatan Mingguan & Laporan — Nabil Hafidz Mubarok
**Nama:** Nabil Hafidz Mubarok  
**NIM:** 10241055  
**Kelompok:** A07  
**Peran:** Infrastruktur, Database & Supervisor  

---

## 🚀 Deliverable Utama Deployment Produksi (Minggu 12)

| Dokumen / Berkas | Deskripsi & Lokasi |
|---|---|
| 📄 **[Catatan & Laporan Minggu 12](./minggu-12.md)** | Laporan lengkap konfigurasi MySQL `kampuslms_prod`, Supervisor worker, pengujian sesi SSH ditutup, tangkapan layar status RUNNING, dan panduan wawancara dosen. |
| ⚙️ **[Supervisor Worker Config](../../deploy/supervisor/kampuslms-worker.conf)** | Berkas konfigurasi `/etc/supervisor/conf.d/kampuslms-worker.conf` siap deploy di VPS. |
| 🗄️ **[MySQL Setup Script](../../deploy/mysql/setup_production_db.sql)** | Skrip SQL inisialisasi basis data `kampuslms_prod` & user terisolasi `kampuslms_user@localhost`. |
| 🛠️ **[Script Otomasi Supervisor](../../scripts/setup-supervisor.sh)** | Skrip bash instalasi, konfigurasi, dan verifikasi status Supervisor di VPS. |
| 🛢️ **[Script Setup MySQL Server](../../scripts/setup-mysql-production.sh)** | Skrip bash otomatisasi pembuatan database, user khusus, password acak 24 karakter, dan output snippet `.env`. |
| 🧪 **[Script Pengujian Queue SSH](../../scripts/test-queue-ssh-closed.sh)** | Skrip pembuktian proses queue tetap memproses notifikasi saat sesi SSH ditutup. |

---

## 📑 Rekap Seluruh Catatan Mingguan

| Minggu | Topik Modul | Status | Tautan Berkas |
|---|---|---|---|
| **Minggu 01** | Fondasi: Alur HTTP Request & Arsitektur Laravel 12 | ✅ Selesai | [minggu-01.md](./minggu-01.md) |
| **Minggu 02** | Route, Controller, View (Blade Component & XSS Prevention) | ✅ Selesai | [minggu-02.md](./minggu-02.md) |
| **Minggu 03** | Database, Migration, Relasi Eloquent & Mass Assignment Defense | ✅ Selesai | [minggu-03.md](./minggu-03.md) |
| **Minggu 04** | Validasi Request, Form Request, Session & CSRF Protection | ✅ Selesai | [minggu-04.md](./minggu-04.md) |
| **Minggu 05** | Autentikasi Pengguna, Hashing Bcrypt & Multi-Role Middleware | ✅ Selesai | [minggu-05.md](./minggu-05.md) |
| **Minggu 06** | Otorisasi Gate & Policy, Pencegahan Kerentanan IDOR | ✅ Selesai | [minggu-06.md](./minggu-06.md) |
| **Minggu 07** | Arsitektur RESTful API, Sanctum Token & Response Formatting | ✅ Selesai | [minggu-07.md](./minggu-07.md) |
| **Minggu 09** | Pengujian Otomatis (PHPUnit Feature Test & Database Refresh) | ✅ Selesai | [minggu-09.md](./minggu-09.md) |
| **Minggu 10** | Asynchronous Processing: Queue, Event, Listener & Notification | ✅ Selesai | [minggu-10.md](./minggu-10.md) |
| **Minggu 11** | Optimasi Performa: Eager Loading, Profiling & Pengindeksan DB | ✅ Selesai | [minggu-11.md](./minggu-11.md) |
| **Minggu 12** | **Deployment Produksi: Database MySQL & Process Manager Supervisor** | 🏆 **Lengkap** | **[minggu-12.md](./minggu-12.md)** |
