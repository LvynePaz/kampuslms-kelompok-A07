## READ

| Halaman | Jumlah query | Waktu (ms) | Memori |
|---------|-------------:|-----------:|-------:|
| Daftar mata kuliah | 7 | 36.52 | 3 mb |
| Detail mata kuliah | 13 | 31.99 | 4 mb |
| Daftar tugas | 6 | 27.02 | 3 mb |
| Daftar pengumpulan (dosen) | 6 | 33.14 | 3 mb |
| **Rekap nilai** | 10 | 39.05 | 4 mb |
| Dashboard mahasiswa | 7 | 32.66 | 3 mb |
| Dashboard dosen | 7 | 30.35 | 3 mb |

---

### Laporan Analisis Performa Aplikasi dan Evaluasi Query (N+1 Problem)

Dokumentasi ini memuat hasil evaluasi performa aplikasi KampusLMS berdasarkan pengujian lingkungan lokal setelah mengeksekusi penyegaran database penuh (`migrate:fresh --seed`). Pemantauan dilakukan menggunakan **Laravel Debugbar** untuk mengukur tiga parameter utama: jumlah query SQL yang dieksekusi, durasi respons halaman (*execution time* dalam milidetik), dan alokasi penggunaan memori.

---

#### 1. Urutan Performa Halaman (Dari Terburuk ke Terbaik)

Berdasarkan data observasi, penentuan urutan performa dilakukan dengan mempertimbangkan dua indikator utama: **waktu eksekusi halaman (*latency*)** dan **efisiensi query ke database**.

1. **Rekap Nilai *(Terburuk)***
* **Metrics:** 10 query | 39.05 ms | 4 MB
* **Deskripsi:** Halaman ini menduduki posisi terburuk dari segi waktu respons (52.41 ms) serta mengonsumsi memori tertinggi (4 MB). Hal ini sejalan dengan perancangan awal skema aplikasi, di mana halaman *Rekap Nilai* melakukan pemrosesan agregasi data yang kompleks, melibatkan kalkulasi relasi nilai dari banyak mahasiswa dan tugas secara bersamaan.

2. **Daftar Mata Kuliah**
* **Metrics:** 7 query | 36.52 ms | 3 MB
* **Deskripsi:** Meskipun memiliki jumlah query yang tergolong efisien (7 query), halaman ini berada di posisi kedua terburuk dari sisi latensi (36.52 ms). Hal ini disebabkan oleh proses pemuatan data ringkasan kurikulum serta daftar pengajar yang membutuhkan penanganan relasi Eloquent.

3. **Daftar Pengumpulan (Dosen)**
* **Metrics:** 6 query | 33.14 ms | 3 MB
* **Deskripsi:** Memiliki waktu eksekusi 33.14 ms. Jumlah query tergolong minimum, tetapi latensi sedikit terangkat karena aplikasi harus memeriksa berkas pengumpulan (*submission*) beserta status penilaian tiap mahasiswa.

4. **Dashboard Mahasiswa**
* **Metrics:** 7 query | 32.66 ms | 3 MB
* **Deskripsi:** Halaman ini memerlukan 7 query dengan waktu respons 32.66 ms untuk menarik data ringkasan kelas yang diikuti, pengumuman terbaru, dan status tugas yang akan datang.

5. **Detail Mata Kuliah**
* **Metrics:** 13 query | 31.99 ms | 4 MB
* **Deskripsi:** Halaman ini mencatatkan **jumlah query terbanyak (13 query)** di antara seluruh halaman. Meskipun waktu responsnya cukup cepat (31.99 ms), struktur query-nya menunjukkan indikasi ketidakefisienan pemanggilan relasi berulang (*N+1 pattern*) pada pemuatan materi dan komponen kelas.

6. **Dashboard Dosen**
* **Metrics:** 7 query | 30.35 ms | 3 MB
* **Deskripsi:** Berjalan dengan waktu eksekusi 30.35 ms dan 7 query, mengindikasikan struktur pemanggilan data ringkasan mengajar yang relatif stabil dan responsif.

7. **Daftar Tugas *(Terbaik)***
* **Metrics:** 6 query | 27.02 ms | 3 MB
* **Deskripsi:** Merupakan halaman paling optimal dengan latensi terendah (27.02 ms) dan pemanggilan query paling minim (6 query).
---

### 2. Analisis Query N+1 pada Halaman Terburuk (Rekap Nilai)
  Pada tab Queries Laravel Debugbar di halaman Rekap Nilai, ditemukan eksekusi query dengan pola berulang di dalam iterasi tabel Blade: 
   ```sql
   select * from "submissions" where "submissions"."user_id" = 3 and "submissions"."assignment_id" = 16 limit 1;
   select * from "grades" where "grades"."submission_id" = 100 limit 1;
   ```
    Penyebab:
  Setiap sel pada baris mahasiswa memanggil `$student->submissions->firstWhere('assignment_id', $assignment->id)` secara langsung tanpa pemuatan relasi diawal (*lazy loading*). Pada kelas dengan 23 mahasiswa dan 3 tugas, aplikasi terpaksa melempar 23 * 3 = 69 query tambahan hanya untuk mengecek tabel `submissions`.

---
## 3. Hasil Analisis Perintah `EXPLAIN` Database

Untuk menganalisis bagaimana mesin database (MySQL/MariaDB) mengeksekusi query yang paling membebani halaman tersebut, salah satu perintah query dipisahkan dan dianalisis menggunakan klausa `EXPLAIN`:

```sql
EXPLAIN SELECT * FROM `submissions` WHERE `assignment_id` = 1 AND `user_id` = 2;
```

##### Hasil Output `EXPLAIN`:

| select_type | table | type | possible_keys | key | rows | Extra |
| --- | --- | --- | --- | --- | --- | --- |
| SIMPLE | submissions | **ALL** | NULL | NULL | **50** | Using where |

### Evaluasi Hasil:

* **Nilai Kolom `type` (`ALL`):**
Hasil `ALL` menandakan bahwa database melakukan *Full Table Scan*. Mesin database terpaksa membaca seluruh baris data di dalam tabel `submissions` dari awal hingga akhir karena tidak adanya indeks (*index*) yang dapat digunakan untuk mempercepat pencarian data berdasarkan `assignment_id` dan `user_id`.
* **Nilai Kolom `rows` (`50`):**
Database harus memeriksa sebanyak 50 baris record hanya untuk menemukan satu pasangan data nilai yang cocok. Seiring bertambahnya data pengumpulan di masa mendatang, angka ini akan terus membengkak dan memperlambat waktu respons secara signifikan.

---

## Kesimpulan dan Rekomendasi Optimasi

1. **Penerapan Eager Loading (`with`):** Pada `GradeController` atau controller terkait halaman rekap nilai, pengambilan data relasi harus diubah dari `Assignment::all()` menjadi `Assignment::with(['submissions.user'])->get()`. Hal ini akan memangkas puluhan query berulang menjadi hanya 2–3 query utama.
2. **Penambahan Indeks Database:** Menambahkan *composite index* pada tabel `submissions` untuk kolom `(assignment_id, user_id)` melalui migrasi Laravel. Langkah ini akan mengubah status `type` pada `EXPLAIN` dari `ALL` menjadi `ref`, sehingga pencarian data langsung mengarah ke baris yang dituju tanpa *Full Table Scan*.

## BREAK

| # | Yang dicoba | Yang harus Anda amati | Prediksi | Yang saya pelajari |
|---|---|---|---|---|
| 1 | Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | Berapa N+1 tersembunyi yang muncul? | Muncul error saat relasi dipanggil tanpa `with()` | `preventLazyLoading()` membantu menemukan N+1 lebih awal |
| 2 | Hapus semua `with()` dari satu controller, ukur ulang | Bandingkan angkanya | Jumlah query meningkat dan waktu halaman menjadi lebih lama | Eager loading mengurangi query berulang |
| 3 | Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | Beda menghitung vs memuat | Memori meningkat karena seluruh data mahasiswa dimuat | `withCount()` lebih hemat karena database hanya mengembalikan jumlah data |
| 4 | Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | `type` berubah menjadi `ALL` | Query dapat berubah menjadi pencarian seluruh tabel | Index membantu database menemukan data tanpa membaca semua baris. Pada SQLite, hasilnya biasanya terlihat sebagai `SCAN` atau `SEARCH`, bukan kolom `type` |
| 5 | Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | Masalah yang tadinya kecil jadi jelas | Waktu, jumlah query, dan penggunaan memori meningkat tajam jika ada N+1 | Data kecil bisa menyembunyikan masalah performa; skala data penting untuk pengujian |
| 6 | Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | **Mengembalikan null** — ingat baik-baik | `env('APP_NAME')` dapat mengembalikan null setelah konfigurasi di-cache | Nilai environment harus diakses melalui file `config`, lalu dipanggil dengan `config()` |
| 7 | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | Data basi yang merugikan pengguna | Nilai lama masih tampil sampai cache kedaluwarsa atau dihapus | Data nilai tidak boleh di-cache sembarangan karena harus selalu terbaru |

## Chechkpoint Minggu 11

- [ ] Buka `docs/performa.md`. Halaman mana yang terburuk sebelum optimisasi? Kenapa halaman itu?
- [ ] Peragakan N+1 di aplikasi Anda: matikan `with()` pada satu halaman dan tunjukkan lonjakan query di Debugbar.
- [ ] Apa beda `withCount('students')` dan `count($course->students)`? Kenapa yang kedua boros?
- [ ] Tunjukkan index yang Anda tambahkan. Jalankan `EXPLAIN` dan buktikan ia terpakai.
- [ ] Sebutkan satu data di aplikasi Anda yang **tidak boleh** di-cache. Kenapa?
- [ ] Apa yang terjadi pada `env()` setelah `config:cache`? Kenapa ini penting minggu depan?