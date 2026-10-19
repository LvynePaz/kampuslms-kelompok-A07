# Catatan Individu Minggu - 09 - Patra Ananda (10241061)

## 9.1 Konsep Unggahan Berkas dan Data Dinamis

### Dua Jenis Berkas di KampusLMS

Pemisahan tempat penyimpanan berkas adalah keputusan arsitektur paling mendasar pada sistem LMS:

| Jenis Berkas | Contoh Berkas | Lokasi Penyimpanan | Jalur Akses | Aturan Keamanan |
|---|---|---|---|---|
| **Publik** | Foto profil pengguna, logo instansi | `storage/app/public` (terhubung ke `public/storage`) | URL statis langsung via web server | Bebas diakses tanpa login dan tanpa Policy |
| **Privat** | Dokumen materi dosen, berkas tugas mahasiswa | `storage/app/private` (disk `local`) | Melalui Controller + Gate Policy | Wajib login, dicek hak aksesnya di server |

Berkas materi dan tugas tidak boleh disimpan di `storage/app/public`. Jika berkas disimpan di sana, siapa pun yang mengetahui polanya bisa mengunduh berkas tanpa login, memotong seluruh Policy Laravel.

---

## 9.3 Read, Break, Fix, Build

### READ: Menelusuri Sistem Berkas di Proyek (30 Menit)

Pengujian dilakukan dengan mengunggah berkas pengumpulan tugas mahasiswa (`tugas-m2-patra.pdf`):

#### 1. Lokasi fisik berkas di server
* **Path lengkap di sistem operasi:**  
  `c:\Users\ASUS TUF\Documents\KULIAH\SMSTR 5\PROWEB\PROJECT\kampuslms-kelompok-A07\storage\app\private\submissions\1\0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Penjelasan:**  
  Berkas masuk ke subfolder `submissions/{assignment_id}/` dengan nama acak 40 karakter hex demi mencegah benturan nama berkas dan serangan path traversal.

#### 2. Perbandingan path di database dengan lokasi fisik
* **Di database (`submissions.file_path`):**  
  `submissions/1/0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Di sistem berkas fisik:**  
  `storage/app/private/submissions/1/0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Relasi:**  
  Database hanya menyimpan path relatif terhadap root disk `local`. Lokasi fisik sesungguhnya ditentukan oleh konfigurasi disk di `config/filesystems.php`.

#### 3. Percobaan akses langsung via browser
* **URL yang dicoba:**  
  `http://kampuslms-kelompok-a07.test/storage/submissions/1/0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Hasil:**  
  Mengembalikan kode status **HTTP 404 Not Found**.
* **Penyebab:**  
  Folder `public/storage` hanya berupa symbolic link ke `storage/app/public`. Folder `storage/app/private` tidak terhubung ke folder `public`, sehingga web server menolak melayani berkas tersebut secara langsung. Berkas privat hanya bisa diunduh jika melewati method `download()` di controller.

#### 4. Perbedaan disk `local` dan `public` di `config/filesystems.php`
* **Disk `local`:**  
  Root folder berada di `storage_path('app/private')`. Tidak memiliki URL publik. Bertujuan menyimpan data sensitif yang membutuhkan otorisasi controller.
* **Disk `public`:**  
  Root folder berada di `storage_path('app/public')` dan memiliki konfigurasi `'url' => env('APP_URL').'/storage'`. Berkas di disk ini bisa dilayani langsung oleh web server seperti Nginx atau Apache.

#### 5. Fungsi `php artisan storage:link`
* **Yang dibuat:**  
  Membuat symlink (tautan simbolis) dari folder `public/storage` yang mengarah ke `storage/app/public`.
* **Alasan materi dan tugas dilarang di sana:**  
  Semua berkas di balik symlink tersebut menjadi dokumen publik. Tidak ada pemeriksaan peran, sesi login, atau kepemilikan data saat URL dikunjungi.

---

### BREAK: Delapan Kerusakan (50 Menit)

Pengujian dengan merusak konfigurasi sistem untuk melihat celah keamanan secara nyata:

| # | Skenario yang Dicoba | Pengamatan Perilaku | Risiko Keamanan dan Pencegahan |
|---|---|---|---|
| **1** | Memindahkan berkas tugas ke `storage/app/public`, lalu salin URL dan buka di Incognito tanpa login. | Berkas tugas PDF langsung terunduh utuh di browser tanpa meminta login. | **Kebocoran data massal.** Mahasiswa bisa menyalin tautan dan membagikannya ke grup luar. Solusi: Gunakan disk `local` (berakar di `storage/app/private`). |
| **2** | Menghapus `Gate::authorize('view', $submission)` dari method `download()`. | Mahasiswa A bisa mengunduh tugas Mahasiswa B hanya dengan mengganti ID submission di URL: `/submissions/2/download`. | **Serangan IDOR (Insecure Direct Object Reference).** Solusi: Pasang `Gate::authorize('view', $submission)` di baris pertama method `download()`. |
| **3** | Menggunakan `storeAs()` dengan nama asli, lalu mengirim nama berkas berbahaya `../../test.txt`. | Berkas berpotensi menembus keluar folder tujuan dan menimpa berkas sistem jika sanitasi path tidak ketat. | **Path Traversal.** Solusi: Selalu gunakan `$file->store()`, bukan `storeAs()`. Simpan nama asli hanya di kolom database `original_name`. |
| **4** | Mengubah nama skrip PHP menjadi `shell.php.pdf`, lalu mengunggahnya pada validasi `extensions` vs `mimes`. | Validasi `extensions:pdf` mengizinkan berkas karena ekstensi akhirnya `.pdf`. Validasi `mimes:pdf` menolak berkas dengan pesan error validasi. | **Bypass File Upload.** Pemeriksaan ekstensi hanya membaca string nama. Aturan `mimes` membaca *magic bytes* (isi fisik berkas). Gunakan selalu `mimes:pdf,doc,docx`. |
| **5** | Mengubah aturan validasi dari `max:5120` menjadi `max:5`, lalu mengunggah berkas ukuran 1 MB. | Unggahan gagal dengan pesan validasi bahwa ukuran berkas melebihi 5 kilobyte. | **Kesalahan satuan kapasitas.** Parameter validasi ukuran berkas di Laravel menggunakan satuan Kilobyte (KB), bukan Megabyte (MB). `max:5120` artinya 5120 KB = 5 MB. |
| **6** | Mengatur `upload_max_filesize = 1M` di `php.ini`, lalu mengunggah berkas 3 MB dengan aturan Laravel `max:5120`. | Server langsung memutus request atau mengembalikan error HTTP 413 / error kosong sebelum validasi Laravel berjalan. | **Batas server mengalahkan batas aplikasi.** Pengaturan `upload_max_filesize` dan `post_max_size` di `php.ini` wajib lebih besar atau sama dengan validasi aplikasi Laravel. |
| **7** | Mengganti `updateOrCreate` menjadi `create` biasa, lalu mengumpulkan tugas untuk kedua kalinya. | Database melempar error SQL `Integrity constraint violation: 1062 Duplicate entry` untuk composite key `(assignment_id, user_id)`. | **Duplikasi pengumpulan tugas.** Composite unique key di database menjaga integritas data, dan `updateOrCreate` di controller memastikan revisi tugas menimpa rekaman lama dengan rapi. |
| **8** | Menghapus baris rekaman submission langsung lewat query SQL di database, lalu mengecek folder penyimpanan. | Baris di database hilang, tetapi berkas fisik PDF masih tersisa di folder `storage/app/private/submissions/1/`. | **Berkas yatim (Orphan Files).** Berkas sampah memenuhi kapasitas server. Solusi: Gunakan event model `deleting` atau hapus berkas fisik secara eksplisit menggunakan `Storage::disk('local')->delete(...)`. |

---

### FIX: Perbaikan Arsitektur Berkas di Laravel 12

Tujuh perbaikan yang diterapkan pada basis kode proyek:

1. **Penyimpanan Privat:** Seluruh berkas materi dan tugas dialihkan menggunakan disk `'local'` (`storage/app/private`).
2. **Otorisasi Server:** Method `download()` memanggil `Gate::authorize('view', $submission)` untuk tugas dan `Gate::authorize('download', $material)` untuk materi.
3. **Nama Berkas Acak:** Menggunakan `$file->store("submissions/{$assignment->id}", 'local')` yang menghasilkan hash acak, memisahkan nama fisik dengan kolom `original_name`.
4. **Validasi Magic Bytes:** Menggunakan aturan `'mimes:pdf,doc,docx'` (bukan sekadar `extensions`).
5. **Koreksi Ukuran Berkas:** Menggunakan kilobyte yang tepat, yaitu `max:5120` (5 MB) untuk submission dan `max:20480` (20 MB) untuk materi dosen.
6. **Pembersihan Berkas Lama:** Saat mahasiswa mengunggah ulang tugas, berkas fisik lama dihapus dari disk sebelum berkas baru disimpan.
7. **Pembersihan Berkas Terkait:** Controller menghapus berkas fisik saat data tugas atau materi dihapus dari sistem.

---

### BUILD: Implementasi Fitur Minggu 9 di Kelompok A07

Berikut rincian implementasi teknis yang dibangun:

#### 1. Unggah Materi Kuliah oleh Dosen
Dosen dapat mengunggah dokumen silabus dan materi pendukung dengan validasi format PDF, PPTX, atau DOCX hingga 20 MB, atau memasukkan tautan eksternal:
* File: `app/Http/Controllers/MaterialController.php`
* Method: `store()`, `update()`, dan Form Request otorisasi.

#### 2. Unduh Materi dengan Otorisasi
Mahasiswa hanya dapat mengunduh materi dari mata kuliah yang mereka ikuti. Tamu dan mahasiswa luar ditolak:
* Policy: `app/Policies/MaterialPolicy.php` method `download()`.
* Alur: `Gate::authorize('download', $material)` memverifikasi pendaftaran mahasiswa di mata kuliah.

#### 3. Pengumpulan Tugas Mahasiswa
* Controller: `app/Http/Controllers/SubmissionController.php` method `store()`.
* Proteksi:
  * Memeriksa `Gate::authorize('submit', $assignment)`.
  * Memeriksa batas waktu: jika `$assignment->allow_late` bernilai false dan waktu saat ini melewati deadline, pengumpulan langsung ditolak.
  * Status keterlambatan dihitung di server: `'is_late' => now()->gt($assignment->due_at)`.
  * Menghapus berkas fisik lama jika mahasiswa melakukan revisi tugas.
  * Memperbarui baris database menggunakan `Submission::updateOrCreate()`.

#### 4. Unduh Berkas Pengumpulan Tugas
* Controller: `app/Http/Controllers/SubmissionController.php` method `download()`.
* Proteksi:
  * `Gate::authorize('view', $submission)` memastikan hanya pemilik tugas, dosen pengampu, dan admin yang dapat mengunduh.
  * Pengecekan fisik: `abort_unless(Storage::disk('local')->exists($submission->file_path), 404)`.
  * Mengembalikan stream download dengan nama asli pengguna: `Storage::disk('local')->download($submission->file_path, $submission->original_name)`.

#### 5. Monitoring Pengumpulan Tugas untuk Dosen
* Halaman: `resources/views/assignments/submissions.blade.php`.
* Fitur: Menampilkan daftar seluruh mahasiswa di kelas, status pengumpulan, waktu submit, label terlambat, ukuran berkas yang mudah dibaca (`Number::fileSize()`), serta tombol unduh privat.

---

### CHECKPOINT 9.4: Rangkuman Evaluasi

1. **Kenapa submission tidak boleh disimpan di `storage/app/public`?**  
   Folder publik dapat diakses langsung oleh siapa saja tanpa melewati autentikasi Laravel. Cukup dengan mengetahui URL berkas, pihak luar atau mahasiswa lain bisa mengunduh tugas teman sekelasnya.
2. **Kenapa nama berkas fisik dibuat acak?**  
   Nama berkas fisik acak mencegah benturan nama jika ada dua mahasiswa mengunggah nama berkas yang sama (`tugas.pdf`), serta menutup celah serangan path traversal seperti `../../nama_file`.
3. **Apa beda validasi `mimes` dan `extensions`?**  
   `extensions` hanya mencocokkan string ekstensi pada nama berkas. `mimes` membaca *magic bytes* atau signature biner di dalam berkas, sehingga skrip berbahaya seperti PHP yang diganti nama menjadi `.pdf` tetap tertolak.
4. **Berapa ukuran `max:5120` dan apa yang bisa membatalkannya di server?**  
   `max:5120` bernilai 5120 KB atau setara dengan 5 MB. Batasan ini bisa gagal jika konfigurasi `upload_max_filesize` atau `post_max_size` pada berkas `php.ini` bernilai lebih kecil dari 5 MB.
5. **Apa yang mencegah mahasiswa mengunduh tugas mahasiswa lain?**  
   Method `download()` memanggil `Gate::authorize('view', $submission)`. Di dalam `SubmissionPolicy@view`, sistem memvalidasi bahwa pengakses harus merupakan pemilik rekaman (`$submission->user_id === $user->id`), dosen pengampu, atau admin.
6. **Kenapa `is_late` dihitung di server?**  
   Waktu pada perangkat klien (browser) bisa dimanipulasi dengan mudah oleh pengguna melalui pengaturan jam sistem operasi. Penghitungan waktu di server (`now()->gt($assignment->due_at)`) menjamin data waktu bersifat valid dan konsisten.
