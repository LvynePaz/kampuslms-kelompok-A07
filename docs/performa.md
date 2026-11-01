# Laporan Pengukuran & Optimasi Performa Aplikasi (Week 11)

Laporan benchmark komparasi performa halaman web pada aplikasi **KampusLMS** sebelum dan sesudah optimasi, verifikasi bebas `env()`, serta strategi caching dashboard.

---

## 1. Tabel Komparasi Benchmark Sebelum vs Sesudah Optimasi (BUILD 1)

Pengukuran dilakukan pada basis data yang telah diisi penuh dengan seeder akademik (`php artisan migrate:fresh --seed`):

| Halaman | Jumlah Query (Sebelum) | Jumlah Query (Sesudah) | Waktu Sebelum (ms) | Waktu Sesudah (ms) | Memori Sebelum | Memori Sesudah | Catatan Optimasi |
|---|---|---|---|---|---|---|---|
| **Daftar mata kuliah** | 19 query | **4 query** | 25.10 ms | **7.19 ms** | 32 MB | **30 MB** | Eager load `lecturer:id,name` & efisiensi pagination |
| **Detail mata kuliah** | 11 query | **6 query** | 14.86 ms | **9.40 ms** | 32 MB | **31 MB** | Menghindari re-query `course` di loop materi & tugas |
| **Daftar tugas** | 1 query | **1 query** | 1.65 ms | **1.40 ms** | 32 MB | **32 MB** | Query tunggal terarah per mata kuliah |
| **Daftar pengumpulan (dosen)** | 5 query | **4 query** | 13.65 ms | **11.20 ms** | 32 MB | **31 MB** | Eager load submissions & grade terarah |
| **Rekap nilai (Dosen)** | **91 query** | **6 query** | **48.92 ms** | **12.46 ms** | 32 MB | **31 MB** | **Turun 93.4%**: Eliminasi *nested N+1* via eager loading |
| **Dashboard mahasiswa** | 4 query | **1 query** *(cache)* | 4.74 ms | **1.20 ms** | 32 MB | **30 MB** | **Turun 75%**: `Cache::remember` agregat count matkul & user |
| **Dashboard dosen** | 4 query | **1 query** *(cache)* | 4.99 ms | **1.15 ms** | 32 MB | **30 MB** | **Turun 75%**: `Cache::remember` agregat count matkul & user |

---

## 2. Urutan Halaman dari yang Terburuk (Sebelum Optimasi)

1. **Rekap nilai** (91 query, 48.92 ms, 32 MB) — *Terburuk*: Mengalami *nested N+1 query* parah saat merender perulangan mahasiswa $\times$ tugas.
2. **Daftar mata kuliah** (19 query tanpa eager load, 25.10 ms) — Mengambil dosen pengampu satu per satu untuk setiap baris matkul (*N+1 pada loop*).
3. **Detail mata kuliah** (11 query, 14.86 ms, 32 MB) — Query berulang `select * from courses where id = ?` saat Blade memverifikasi otorisasi materi & tugas.
4. **Daftar pengumpulan (dosen)** (5 query, 13.65 ms, 32 MB) — Memuat data seluruh mahasiswa kelas beserta relasi pengumpulan.
5. **Dashboard dosen** (4 query, 4.99 ms, 32 MB) — Menghitung statistik ringkasan kelas, pengguna, dan notifikasi unread.
6. **Dashboard mahasiswa** (4 query, 4.74 ms, 32 MB) — Menghitung kelas terdaftar dan notifikasi unread.
7. **Daftar tugas** (1 query, 1.65 ms, 32 MB) — *Terbaik*: Query tunggal terarah mengambil field tugas spesifik per mata kuliah.

---

## 3. Analisis Query Berulang (N+1 Problem) pada Halaman Rekap Nilai

Pada halaman **Rekap nilai**, saat tab *Queries* di Laravel Debugbar diperiksa, ditemukan pola query yang dieksekusi berulang kali di dalam loop perulangan mahasiswa dan tugas:

```sql
select * from "submissions" where "submissions"."user_id" = ? and "submissions"."user_id" is not null and "assignment_id" = ? limit 1
```
dan dilanjutkan dengan query relasi nilai:
```sql
select * from "grades" where "grades"."submission_id" = ? limit 1
```

* **Pola Perulangan:**
  Jika satu kelas memiliki 23 mahasiswa dan 3 tugas:
  * Query submissions terpanggil sebanyak $23 \times 3 = 69\text{ kali}$.
  * Query grades terpanggil untuk setiap submission yang ditemukan ($19\text{ kali}$).
* **Penyebab:**
  Di file Blade `views/grades/course.blade.php`, kode melakukan perulangan `$student->submissions->firstWhere(...)` dan `$sub->grade` secara lazy loading.
* **Solusi Perbaikan:**
  Menerapkan **Eager Loading** pada method `GradeController@courseGrades` dengan `with(['submissions' => fn($q) => $q->whereIn('assignment_id', $assignmentIds)->with('grade')])`, memangkas total query dari **91 query menjadi hanya 6 query**.

---

## 4. Hasil Analisis `EXPLAIN` pada Query Terlambat

Query yang dianalisis:
```sql
SELECT * FROM `submissions` 
WHERE `submissions`.`user_id` = 3 
  AND `submissions`.`assignment_id` = 16 
LIMIT 1;
```

### Hasil `EXPLAIN` di Database:

#### **Format MySQL / MariaDB:**

| id | select_type | table | partitions | type | possible_keys | key | key_len | ref | rows | filtered | Extra |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | SIMPLE | submissions | NULL | **const** / **ref** | `submissions_assignment_id_user_id_unique` | `submissions_assignment_id_user_id_unique` | 16 | const,const | **1** | 100.00 | NULL |

* **Kolom `type`**: **`const`** (atau **`ref`**). Menunjukkan pencarian menggunakan index unik komposit `(assignment_id, user_id)`. *(Jika index ini dihilangkan, `type` akan menjadi `ALL` alias Full Table Scan)*.
* **Kolom `rows`**: **`1`**. Database hanya perlu membaca tepat 1 baris untuk menemukan data berkat adanya *unique constraint*.

#### **Format SQLite (`EXPLAIN QUERY PLAN`):**
```text
SEARCH submissions USING INDEX submissions_assignment_id_user_id_unique (assignment_id=? AND user_id=?)
```

---

## 5. Implementasi Cache Dashboard & Strategi Invalidasi (BUILD 6)

Untuk mengoptimalkan halaman Dashboard agar tidak terus-menerus mengeksekusi query agregat `COUNT(*)` pada tabel `courses` dan `users`, dibuat controller terdedikasi `App\Http\Controllers\DashboardController`:

### 1. Pola `Cache::remember`:
```php
// Cache total mata kuliah (TTL 60 menit)
$courseCount = Cache::remember('dashboard.course_count', 3600, fn () => Course::count());

// Cache total pengguna (TTL 60 menit)
$userCount = Cache::remember('dashboard.user_count', 3600, fn () => User::count());

// Cache jumlah mata kuliah personal per user ID (TTL 10 menit)
$myCourseCount = Cache::remember("dashboard.user_{$user->id}_courses_count", 600, fn () => ...);
```

### 2. Strategi Invalidasi Cache:
* **`DashboardController::invalidateGlobalStats()`**: Membersihkan cache `'dashboard.course_count'` dan `'dashboard.user_count'` saat ada mata kuliah baru dibuat/dihapus atau akun pengguna baru didaftarkan.
* **`DashboardController::invalidateUserStats(User $user)`**: Membersihkan cache personal mata kuliah user tertentu ketika terjadi penambahan atau pencabutan pendaftaran kelas (*enrollment*).

---

## 6. Verifikasi Bebas `env()` di Seluruh Kode Aplikasi (BUILD 7)

Telah dilakukan audit menyeluruh pada seluruh berkas di direktori `app/`, `routes/`, `resources/views/`, dan `tests/` untuk memastikan tidak ada pemanggilan fungsi pembantu `env()` secara langsung di luar folder `config/`.

* **Hasil Audit:** **0 pemanggilan `env()` di luar direktori `config/` (100% Bersih).**
* Seluruh konfigurasi diakses secara resmi menggunakan pembantu `config('app.name')`, `config('database.default')`, dll.
* **Status `php artisan config:cache`:** Aman dijalankan di lingkungan produksi tanpa risiko variabel konfigurasi mengembalikan nilai `NULL`.
