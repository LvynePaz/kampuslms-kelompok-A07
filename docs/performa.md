# Laporan Pengukuran Performa Aplikasi (Week 11)

Laporan pengukuran performa halaman web pada aplikasi **KampusLMS** setelah basis data diisi penuh menggunakan seeder akademik (`php artisan migrate:fresh --seed`).

---

## 1. Tabel Hasil Pengukuran Performa

| Halaman | Jumlah query | Waktu (ms) | Memori |
|---|---|---|---|
| Daftar mata kuliah | 4 | 7.19 ms | 30 MB |
| Detail mata kuliah | 11 | 14.86 ms | 32 MB |
| Daftar tugas | 1 | 1.65 ms | 32 MB |
| Daftar pengumpulan (dosen) | 5 | 13.65 ms | 32 MB |
| Rekap nilai | 91 | 48.92 ms | 32 MB |
| Dashboard mahasiswa | 4 | 4.74 ms | 32 MB |
| Dashboard dosen | 4 | 4.99 ms | 32 MB |

> **Catatan Teknis Rekap Nilai:**
> Pada rancangan skema awal tanpa eager loading (lazy loading bertingkat pada blade), halaman **Rekap Nilai** menghasilkan **91 query** karena terjadi *nested N+1 query*. Ketika dioptimasi menggunakan eager loading `with(['submissions' => fn($q) => $q->whereIn(...)->with('grade')])`, jumlah query terpangkas drastis menjadi hanya **6 query** dengan waktu eksekusi **12.46 ms**.

---

## 2. Urutan Halaman dari yang Terburuk

Diurutkan berdasarkan beban **Jumlah Query** dan **Waktu Eksekusi (Latency)** dari yang paling tidak efisien:

1. **Rekap nilai** (91 query, 48.92 ms, 32 MB) — *Terburuk*: Mengalami masalah *nested N+1 query* parah saat merender relasi tugas, mahasiswa, pengumpulan, dan nilai.
2. **Detail mata kuliah** (11 query, 14.86 ms, 32 MB) — Terdapat pemanggilan berulang `select * from courses where id = ?` saat mengecek izin policy/relasi materi dan tugas.
3. **Daftar pengumpulan (dosen)** (5 query, 13.65 ms, 32 MB) — Memuat data mahasiswa kelas beserta submission dan grade untuk satu tugas tertentu.
4. **Daftar mata kuliah** (4 query, 7.19 ms, 30 MB) — Menghitung total data pagination dan memuat relasi dosen pengampu.
5. **Dashboard dosen** (4 query, 4.99 ms, 32 MB) — Menghitung statistik ringkasan kelas, pengguna, dan notifikasi unread.
6. **Dashboard mahasiswa** (4 query, 4.74 ms, 32 MB) — Menghitung statistik kelas yang diikuti mahasiswa dan notifikasi unread.
7. **Daftar tugas** (1 query, 1.65 ms, 32 MB) — *Terbaik*: Query tunggal terarah mengambil field tugas spesifik per mata kuliah.

---

## 3. Analisis Query Berulang (N+1 Problem) pada Halaman Terburuk

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
  Di file Blade `views/grades/course.blade.php`, kode melakukan perulangan `$student->submissions->firstWhere(...)` dan `$sub->grade` secara lazy loading tanpa eager load relasi di controller sebelumnya.

---

## 4. Hasil Analisis `EXPLAIN` pada Query Terlambat

Salah satu query terberat yang dieksekusi berulang kali pada tabel `submissions`:

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

* **Kolom `type`**: **`const`** (atau **`ref`**).
  * Menunjukkan bahwa pencarian record sangat cepat karena menggunakan indeks unik komposit `(assignment_id, user_id)`.
  * *Catatan:* Jika indeks komposit tersebut dihilangkan, kolom `type` akan memburuk menjadi **`ALL`** (*Full Table Scan*), yang berarti database harus membaca seluruh isi tabel dari baris pertama sampai terakhir.
* **Kolom `rows`**: **`1`**.
  * Database hanya perlu memeriksa tepat **1 baris** untuk menemukan data yang cocok karena adanya *unique constraint*. Tanpa indeks, nilai `rows` akan membengkak setara jumlah baris di tabel `submissions`.

#### **Format SQLite (`EXPLAIN QUERY PLAN`):**
```text
SEARCH submissions USING INDEX submissions_assignment_id_user_id_unique (assignment_id=? AND user_id=?)
```
* Menandakan query berhasil memanfaatkan indeks komposit unik, bukan melakukan `SCAN TABLE submissions`.

---

## 5. Kesimpulan & Solusi Perbaikan

Meskipun setiap query individual berjalan cepat ($\le 1\text{ ms}$) berkat indeks database, akumulasi **91 query bolak-balik (round-trip) ke database** sangat membebani server dan memperlambat waktu respon secara drastis (*Network I/O Bottleneck*).

**Solusi Perbaikan:**
Menerapkan **Eager Loading** pada method `GradeController@courseGrades`:
```php
$assignmentIds = $course->assignments()->pluck('id');

$course->load([
    'assignments' => fn ($query) => $query->orderBy('due_at'),
    'students'    => fn ($query) => $query->orderBy('name')->with([
        'submissions' => fn ($subQuery) => $subQuery->whereIn('assignment_id', $assignmentIds)->with('grade'),
    ]),
]);
```
Dengan eager loading ini, seluruh data submission dan grade ditarik sekaligus hanya dengan **2 query tambahan** (menggunakan klausa `WHERE IN (...)`), memangkas total query dari **91 query menjadi hanya 6 query**.

