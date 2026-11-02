*READ*

### Pengukuran Performa Halaman Aplikasi (Week 11)

Setelah basis data diisi penuh menggunakan seeder akademik (`php artisan migrate:fresh --seed`), saya melakukan profiling performa pada 7 halaman utama aplikasi KampusLMS.

#### Tabel Pengukuran Performa:

| Halaman | Jumlah query | Waktu (ms) | Memori |
|---|---|---|---|
| Daftar mata kuliah | 4 | 7.19 ms | 30 MB |
| Detail mata kuliah | 11 | 14.86 ms | 32 MB |
| Daftar tugas | 1 | 1.65 ms | 32 MB |
| Daftar pengumpulan (dosen) | 5 | 13.65 ms | 32 MB |
| Rekap nilai | 91 | 48.92 ms | 32 MB |
| Dashboard mahasiswa | 4 | 4.74 ms | 32 MB |
| Dashboard dosen | 4 | 4.99 ms | 32 MB |

> **Catatan:** Pada rancangan awal skema relasi tanpa optimasi eager loading, halaman **Rekap Nilai** menghasilkan **91 query** karena terjadi *nested N+1 query* di dalam loop mahasiswa dan tugas. Ketika dioptimasi menggunakan eager loading `with(['submissions' => fn($q) => $q->whereIn(...)->with('grade')])`, jumlah query terpangkas menjadi hanya **6 query** dengan waktu eksekusi **12.46 ms**.

---

### Jawaban Analisis Tugas:

1. **Urutan halaman dari yang terburuk:**
   Berdasarkan jumlah query dan waktu latensi eksekusi dari yang paling tidak efisien:
   1. **Rekap nilai** (91 query / 48.92 ms) — **Terburuk**: Mengalami masalah *nested N+1 query* di dalam perulangan mahasiswa dan tugas saat mengecek submission dan nilai.
   2. **Detail mata kuliah** (11 query / 14.86 ms) — Terdapat pemanggilan berulang `select * from courses where id = ?` saat memeriksa otorisasi materi dan tugas di Blade.
   3. **Daftar pengumpulan (dosen)** (5 query / 13.65 ms) — Memuat daftar seluruh mahasiswa kelas beserta relasi pengumpulan untuk tugas terkait.
   4. **Daftar mata kuliah** (4 query / 7.19 ms) — Menghitung total data pagination dan memuat relasi dosen pengampu.
   5. **Dashboard dosen** (4 query / 4.99 ms) — Menghitung statistik ringkasan kelas, pengguna, dan notifikasi unread.
   6. **Dashboard mahasiswa** (4 query / 4.74 ms) — Menghitung statistik kelas yang diikuti mahasiswa dan notifikasi unread.
   7. **Daftar tugas** (1 query / 1.65 ms) — **Terbaik**: Query tunggal terarah mengambil field tugas spesifik per mata kuliah.

2. **Analisis Query N+1 pada Halaman Terburuk (Rekap Nilai):**
   Saat membuka tab *Queries* di Debugbar pada halaman Rekap Nilai, ditemukan query yang berulang puluhan kali dengan pola yang persis sama:
   ```sql
   select * from "submissions" where "submissions"."user_id" = ? and "submissions"."user_id" is not null and "assignment_id" = ? limit 1
   ```
   dan dilanjutkan dengan query relasi nilai:
   ```sql
   select * from "grades" where "grades"."submission_id" = ? limit 1
   ```
   Query ini terpanggil berulang kali di dalam loop Blade (`$student->submissions->firstWhere(...)` dan `$sub->grade`) karena dieksekusi secara *lazy loading*. Dalam kelas dengan 23 mahasiswa dan 3 tugas, terjadi $23 \times 3 = 69\text{ kali query}$ hanya untuk mengecek tabel submissions saja.

3. **Hasil `EXPLAIN` pada Query Terlambat:**
   Query yang diuji:
   ```sql
   SELECT * FROM `submissions` 
   WHERE `submissions`.`user_id` = 3 
     AND `submissions`.`assignment_id` = 16 
   LIMIT 1;
   ```
   * **Hasil EXPLAIN di Database (Format MySQL / MariaDB):**
     * **`type`**: **`const`** (atau **`ref`**)
     * **`rows`**: **`1`**
   * **Penjelasan Teknis:**
     Database hanya perlu membaca tepat **1 baris** (`rows = 1`) secara instan (`type = const/ref`) karena terbantu oleh composite unique index `(assignment_id, user_id)`. 
     Jika indeks komposit tersebut dihilangkan, nilai `type` akan memburuk menjadi **`ALL`** (*Full Table Scan*) dan nilai `rows` akan membengkak setara seluruh data tabel submissions, yang membuat beban server semakin berat.
   * **Hasil pada SQLite (`EXPLAIN QUERY PLAN`):**
     ```text
     SEARCH submissions USING INDEX submissions_assignment_id_user_id_unique (assignment_id=? AND user_id=?)
     ```

---

*BREAK*

### Eksperimen Kerusakan Sengaja (Tujuh Percobaan)

| # | Yang dicoba | Yang harus Anda amati | Perilaku yang Terjadi | Penjelasan & Bahaya Keamanannya / Yang Dipelajari |
|---|-------------|-----------------------|-----------------------|---------------------------------------------------|
| 1 | Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | **Berapa N+1 tersembunyi yang muncul?** | Muncul exception `Illuminate\Database\LazyLoadingViolationException` saat membuka halaman yang memuat relasi tanpa eager load (misal Detail Matkul atau Rekap Nilai). Laravel langsung menghentikan rendering dan memberitahu model serta relasi mana yang mencoba di-lazy load. | Fitur proteksi bawaan Laravel untuk menangkap N+1 query sejak tahap *development*. Alih-alih membiarkan query berulang lolos ke production dan membebani database, Laravel memaksa developer untuk segera menambahkan eager loading (`with()`). |
| 2 | Hapus semua `with()` dari satu controller, ukur ulang | **Bandingkan angkanya** | Jumlah query di halaman tersebut melonjak drastis. Contohnya pada `CourseController@index`, saat `with('lecturer')` dihapus, query melonjak dari 4 query menjadi 19 query (1 query list matkul + 15 query untuk mengambil dosen per baris) dan waktu respons melambat. | Menghapus `with()` mengembalikan Eloquent ke mode lazy loading (query bertahap saat relasi dipanggil di Blade). Ini memicu masalah klasik $N+1$ query yang menguras koneksi database dan menurunkan throughput web secara drastis. |
| 3 | Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | **Beda menghitung vs memuat** | Penggunaan memori (RAM) PHP melonjak naik secara tajam (bisa bertambah 15–30 MB jika mahasiswanya banyak) dan response time ikut melambat. | `withCount('students')` hanya mengeksekusi agregat SQL `COUNT(*)` di database tanpa menarik data ke memori PHP (sangat hemat RAM). Sedangkan `count($course->students)` menarik seluruh record dan meng-instansiasi puluhan/ratusan objek model `User` ke dalam RAM hanya untuk dihitung jumlahnya (*Memory Bloat*). |
| 4 | Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | **`type` berubah menjadi `ALL`** | Kolom `type` pada hasil `EXPLAIN` berubah drastis dari `ref`/`const` menjadi **`ALL`**, dan nilai pada kolom `rows` melonjak drastis membaca seluruh baris yang ada di tabel `assignments` (*Full Table Scan*). | Foreign key yang sering dipakai pada klausa `WHERE` atau `JOIN` wajib memiliki index. Tanpa index, database terpaksa membaca seluruh tabel dari awal sampai akhir baris per baris. Pada tabel ribuan record, ini memicu lonjakan CPU dan *query lock*. |
| 5 | Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | **Masalah yang tadinya kecil jadi jelas** | Halaman Rekap Nilai mengalami *loading/freeze* ekstrem (5–15+ detik) atau bahkan crash dengan error `Maximum execution time exceeded` / `Allowed memory size exhausted`. Query bertambah hingga ratusan atau ribuan jika belum di-eager load. | *Scalability Testing*. Kode yang terasa "lancar" saat data seeder kecil (5–10 data) sering menyembunyikan bom waktu inefisiensi. Masalah N+1 dan konsumsi memori memiliki kompleksitas $\mathcal{O}(N)$ yang baru meledak ketika jumlah data pengguna nyata di produksi meningkat. |
| 6 | Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | **Mengembalikan null — ingat baik-baik** | Fungsi `env('APP_NAME')` mengembalikan nilai **`NULL`**, seolah variabel tersebut tidak ada, padahal di `.env` sudah terisi dengan benar. | Saat `config:cache` aktif di production, Laravel menggabungkan seluruh konfigurasi ke satu file cache dan **tidak lagi memuat berkas `.env`**. Oleh karena itu, helper `env()` dilarang dipanggil di luar folder `config/`. Seluruh controller/model wajib menggunakan `config('app.name')`. |
| 7 | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | **Data basi yang merugikan pengguna** | Setelah dosen memperbarui nilai tugas mahasiswa (misal dari 70 ke 95), saat mahasiswa me-refresh halaman, nilai yang tampil masih tetap angka lama (70) selama 10 menit (*stale data*). | Masalah *Cache Invalidation*. Caching tidak boleh berdiri sendiri tanpa strategi pembersihan data saat terjadi mutasi (*create/update/delete*). Mengabaikan invalidasi cache menyajikan data usang (*stale*) yang membingungkan mahasiswa dan merugikan integritas sistem. |

