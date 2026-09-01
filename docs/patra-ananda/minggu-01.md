# Catatan Individu Minggu 01 - Patra Ananda (10241061)

## 1. Progres Pengerjaan
- Inisialisasi dan setup awal framework Laravel 12.
- Pengaturan Git repository dan branching kelompok.
- Mempelajari alur HTTP Request dan struktur direktori Laravel 12.
- Pembuatan dan pengujian route `/tentang` beserta tampilan profil kelompok.

---

## 2. Hasil Eksplorasi (Read - Break - Fix)
- **public/index.php**: Berfungsi sebagai satu-satunya pintu masuk request browser ke aplikasi Laravel. Memuat autoloader dan mengeksekusi siklus penanganan request.
- **bootstrap/app.php**: Pusat konfigurasi di Laravel 12 untuk routing, middleware, dan exception (menggantikan Kernel.php di versi lama).
- **Keamanan `.env`**: Menyimpan kredensial rahasia (database, API key) dan tidak boleh di-commit ke Git, supaya tidak bisa di tau oleh orang lain yang bisa menyalagunakan untuk merusak isi dari projek ini.

### Tabel Eksperimen Kerusakan (BREAK)
| # | Yang Dirusak | Prediksi Sebelum Mencoba | Pesan Error Sebenarnya | Pembelajaran / Insight |
|---|---|---|---|---|
| **1** | Ganti nama `.env` menjadi `.env.bak` | Aplikasi gagal membaca environment/database dan menggunakan fallback config | Terjadi kegagalan konfigurasi kunci aplikasi / error konfigurasi default | File `.env` sangat krusial saat runtime untuk menyimpan data dinamis lingkungan lokal. |
| **2** | Kosongkan nilai `APP_KEY` di `.env` | Aplikasi melempar error enkripsi/session gagal | `RuntimeException: No application encryption key has been specified.` | `APP_KEY` wajib ada untuk mengamankan enkripsi session, cookie, dan password payload. |
| **3** | Ubah `DB_DATABASE` ke nama yang tidak ada | Gagal koneksi saat ada query ke database | `PDOException: Unknown database '...'` (saat query database dijalankan) | Database connection ditolak jika nama schema belum dibuat di MySQL/PostgreSQL. |
| **4** | Ubah `APP_DEBUG=false`, lalu ulangi no. 3 | Error trace disembunyikan dan diganti halaman generic | `500 Server Error` (halaman polos tanpa trace konfigurasi) | `APP_DEBUG=false` mutlak untuk produksi agar rahasia kredensial database tidak bocor ke publik. |

---

## 3. Catatan Penting Spesifikasi Proyek (Poin Kunci Interview)
1. **Keamanan Data Mahasiswa:** Menggunakan *Laravel Policy & Authorization* (Status 403) agar mahasiswa tidak bisa mengakses submission mahasiswa lain via URL.
2. **Tabel `course_user` (Unique Composite):** Mencegah seorang mahasiswa mendaftar (enroll) 2 kali di mata kuliah yang sama.
3. **Optimasi Rekap Nilai:** Menghindari masalah *N+1 Query* dengan menerapkan *Eager Loading* (`with()`).
4. **Queue Worker:** Jika worker mati, notifikasi tetap aman tersimpan di tabel `jobs` dan diproses saat worker aktif kembali.
5. **Penyimpanan File Submission:** Disimpan di `storage/app/private` (bukan public) agar tidak bisa diunduh sembarang orang tanpa login.
6. **Verifikasi Kode AI:** Memastikan setiap kode dari AI disesuaikan dengan arsitektur **Laravel 12** dan spesifikasi dosen.

---

## 4. CHECKPOINT — Pertanyaan Mandiri
- [x] Alur request dari browser sampai HTML kembali.
- [x] Alasan hanya folder `public/` yang diekspos ke internet.
- [x] Perbedaan `.env` dan `.env.example`.
- [x] Pendaftaran middleware di Laravel 12 pada `bootstrap/app.php`.
- [x] Risiko `APP_DEBUG=true` di lingkungan production.
