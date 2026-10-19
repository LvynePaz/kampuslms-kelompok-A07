# Catatan Individu Minggu - 09 - Patra Ananda (10241061)


---

## 9.3 Read, Break, Fix, Build

### READ: Menelusuri Jejak Satu Berkas di Sistem (30 Menit)

Saya menguji alur sistem dengan mengunggah satu berkas pengumpulan tugas mahasiswa (`tugas-m2-patra.pdf`):

#### 1. Di mana lokasi fisik berkas tersebut tersimpan di harddisk?
* **Jalur lengkap di komputer server:**  
  `c:\Users\ASUS TUF\Documents\KULIAH\SMSTR 5\PROWEB\PROJECT\kampuslms-kelompok-A07\storage\app\private\submissions\1\0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Penjelasan:**  
  Berkas fisik masuk ke dalam subfolder `submissions/{id_tugas}/` dengan nama acak sepanjang 40 karakter. Sistem sengaja tidak menggunakan nama asli mahasiswa demi menjaga keamanan harddisk server.

#### 2. Bandingkan path di database dengan lokasi fisik di harddisk
* **Di tabel database (`submissions.file_path`):**  
  `submissions/1/0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Di harddisk komputer:**  
  `storage/app/private/submissions/1/0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Hubungan keduanya:**  
  Database hanya mencatat alamat relatif dari folder utama disk `local`. Lokasi fisik lengkapnya diatur otomatis oleh konfigurasi Laravel di `config/filesystems.php`.

#### 3. Coba akses berkas langsung lewat URL browser
* **URL yang dicoba:**  
  `http://kampuslms-kelompok-a07.test/storage/submissions/1/0RmruW1xozlyFx935vL1a58ElFUrjy6MQARXzMnu.pdf`
* **Hasil:**  
  Muncul tampilan **HTTP 404 Not Found** (Halaman Tidak Ditemukan).
* **Kenapa gagal?**  
  Karena tautan web `/storage/...` hanya mengarah ke folder `storage/app/public`. Folder privat (`storage/app/private`) sengaja disembunyikan dan tidak memiliki pintu keluar langsung ke web server. Satu-satunya cara mengunduh berkas ini adalah melalui controller resmi aplikasi.

#### 4. Apa beda disk `local` dan `public` di `config/filesystems.php`?
* **Disk `local`:**  
  Mengarah ke folder `storage/app/private`. Disk ini tidak memiliki konfigurasi URL publik. Berkas di sini hanya bisa dibaca oleh script PHP di server.
* **Disk `public`:**  
  Mengarah ke folder `storage/app/public` dan memiliki alamat web `'url' => env('APP_URL').'/storage'`. Web server seperti Nginx atau Apache bisa langsung menyajikan berkas ini ke pengunjung tanpa memanggil PHP.

#### 5. Apa fungsi perintah `php artisan storage:link`?
* **Yang dikerjakan perintah ini:**  
  Membuat jalan pintas (shortcut / symbolic link) dari folder luar `public/storage` ke folder dalam `storage/app/public`.
* **Kenapa berkas tugas tidak boleh ada di sana?**  
  Karena semua berkas di balik jalan pintas tersebut menjadi milik umum. Tidak ada proses login, tidak ada sesi, dan tidak ada aturan Policy yang bisa menghalangi orang untuk mengunduhnya.

---

### BREAK: Menguji Delapan Skenario Kerusakan (50 Menit)

Berikut hasil pengujian langsung saat saya sengaja merusak atau mematikan fitur keamanan:

| # | Skenario yang Dicoba | Apa yang Terjadi di Layar | Bahaya dan Cara Mencegahnya |
|---|---|---|---|
| **1** | Memindahkan berkas tugas ke `storage/app/public`, lalu membuka tautannya di jendela penyamaran (Incognito) tanpa login. | Berkas PDF langsung terunduh lancar tanpa meminta login sama sekali. | **Kebocoran data tugas kelas.** Orang luar atau mahasiswa lain bisa mencuri tugas dengan mudah. Pencegahan: Simpan selalu berkas tugas di disk `local` (`storage/app/private`). |
| **2** | Menghapus baris `Gate::authorize('view', $submission)` dari fungsi download di controller. | Mahasiswa A bisa mengunduh tugas milik Mahasiswa B hanya dengan mengganti angka ID di URL (misal dari `/submissions/1/download` menjadi `/submissions/2/download`). | **Celah IDOR (Insecure Direct Object Reference).** Siapa pun bisa mengintip berkas orang lain. Pencegahan: Pasang selalu `Gate::authorize('view', $submission)` sebelum file dikirim. |
| **3** | Menyimpan berkas memakai fungsi `storeAs()` dengan nama asli, lalu mencoba mengunggah nama berkas berbahaya seperti `../../dokumen_rahasia.txt`. | Berkas berisiko keluar dari folder yang ditentukan dan menimpa dokumen penting milik sistem. | **Serangan Path Traversal.** Tanda `../` berarti mundur satu folder. Pencegahan: Selalu gunakan fungsi bawaan `$file->store()`, karena fungsi ini otomatis mengacak nama berkas menjadi hash unik yang aman. |
| **4** | Mengubah nama skrip PHP jahat menjadi `hack.php.pdf`, lalu menguji aturan `extensions` dibandingkan dengan `mimes`. | Validasi `extensions:pdf` terkecoh dan meloloskan berkas karena nama akhirnya `.pdf`. Sebaliknya, aturan `mimes:pdf` menolak berkas tersebut. | **Penyusupan berkas berbahaya.** Aturan `extensions` cuma membaca teks di nama berkas (seperti membaca stiker di botol). Aturan `mimes` membaca tanda tangan biner asli (*magic bytes*). Pencegahan: Gunakan selalu validasi `mimes:pdf,doc,docx`. |
| **5** | Mengubah aturan validasi dari `max:5120` menjadi `max:5`, lalu mencoba mengunggah berkas ukuran 1 MB. | Unggahan langsung ditolak dengan pesan bahwa ukuran berkas melebihi 5 kilobyte. | **Salah paham satuan ukuran.** Di Laravel, angka pada aturan `max` dihitung dalam satuan Kilobyte (KB), bukan Megabyte (MB). `max:5120` artinya 5120 KB = 5 MB. Kalau ditulis `max:5`, artinya cuma 5 KB. |
| **6** | Mengatur `upload_max_filesize = 1M` di konfigurasi server `php.ini`, lalu mengunggah berkas 3 MB dengan aturan Laravel `max:5120`. | Layar langsung memunculkan pesan error kosong atau gagal kirim sebelum validasi Laravel sempat berjalan. | **Batas server mengalahkan batas aplikasi.** Satpam di gerbang kampus (`php.ini`) lebih galak daripada satpam ruang kelas (Laravel). Pastikan `upload_max_filesize` dan `post_max_size` di server disetel lebih besar atau sama dengan batas aplikasi. |
| **7** | Mengganti perintah `updateOrCreate` menjadi `create` biasa, lalu mencoba mengumpulkan tugas untuk kedua kalinya. | Layar memunculkan error database `Duplicate entry` untuk pasangan `(assignment_id, user_id)`. | **Data ganda yang merusak sistem.** Kunci unik di database menjaga agar satu mahasiswa hanya punya satu tugas per tugas kuliah. Perintah `updateOrCreate` memastikan pengumpulan ulang akan memperbarui tugas lama secara rapi. |
| **8** | Menghapus data submission langsung lewat query SQL database, lalu memeriksa folder penyimpanan berkas di komputer. | Baris di database hilang, tetapi berkas fisik PDF masih tertinggal menumpuk di folder `storage/app/private/`. | **Berkas yatim (Orphan Files).** Berkas yang tidak punya pemilik akan memakan kapasitas harddisk selamanya. Pencegahan: Hapus berkas fisik menggunakan fungsi `Storage::disk('local')->delete(...)` sebelum datanya dihapus dari database. |

---

#### Pelajaran Paling Kritis dari Eksperimen Break:

Poin **Nomor 1 dan 2** membuktikan hal yang paling penting:  
Banyak orang mengira jika tombol "Unduh" sudah disembunyikan dari layar mahasiswa lain, aplikasi sudah aman. Itu salah besar. Pengguna yang paham sedikit teknis bisa langsung menebak tautan berkas atau menembak URL download melalui Postman atau cURL. Keamanan berkas wajib dijaga di sisi server dengan menaruh berkas di folder privat dan memanggil `Gate::authorize()` sebelum berkas dikirimkan.

---

## 9.3 FIX: Solusi Arsitektur Berkas di Laravel 12

Tujuh perbaikan yang telah diterapkan pada repositori KampusLMS Kelompok A07:

1. **Memindahkan ke Disk Privat:** Seluruh berkas tugas dan materi disimpan menggunakan disk `'local'` (`storage/app/private`).
2. **Menjaga Akses dengan Policy:** Method `download()` selalu memanggil otorisasi resmi:
   ```php
   Gate::authorize('view', $submission);
   ```
3. **Mengacak Nama Berkas Fisik:** Menggunakan `$file->store("submissions/{$assignment->id}", 'local')`. Nama acak ini menutup celah nama ganda dan path traversal. Nama asli disimpan terpisah di kolom `original_name`.
4. **Validasi Isi Asli Berkas (Mimes):** Validasi menggunakan `'mimes:pdf,doc,docx'` agar sistem memeriksa struktur biner berkas, bukan sekadar tulisan ekstensinya.
5. **Memperbaiki Hitungan Ukuran Berkas:** Menggunakan angka Kilobyte yang tepat: `max:5120` untuk tugas (5 MB) dan `max:20480` untuk materi dosen (20 MB).
6. **Menghapus Berkas Fisik Lama saat Revisi Tugas:** Saat mahasiswa mengumpulkan ulang tugas, berkas fisik yang lama langsung dihapus dari harddisk sebelum berkas baru disimpan.
7. **Membersihkan Berkas saat Data Dihapus:** Saat tugas atau mata kuliah dihapus, controller menghapus berkas-berkas fisiknya dari disk agar tidak menjadi berkas yatim.

---

## 9.3 BUILD: Penerapan Fitur Minggu 9 di Kelompok A07

Berikut pekerjaan yang berhasil diselesaikan di branch `patra_61` dan `indah_59`:

#### 1. Unggah Materi Kuliah oleh Dosen
* **Lokasi Berkas:** `app/Http/Controllers/MaterialController.php`
* **Cara Kerja:** Dosen bisa mengunggah berkas materi berformat PDF, PPTX, atau DOCX hingga ukuran 20 MB, atau memasukkan tautan eksternal (misal Google Drive atau YouTube). Berkas fisik otomatis disimpan di disk privat `materials/`.

#### 2. Unduh Materi Berizin
* **Lokasi Berkas:** `app/Policies/MaterialPolicy.php` dan `MaterialController@download`
* **Cara Kerja:** Hanya mahasiswa yang benar-benar mengambil mata kuliah tersebut yang boleh mengunduh materi. Mahasiswa dari kelas lain atau tamu luar otomatis ditolak dengan pesan 403 Forbidden.

#### 3. Pengumpulan Tugas Mahasiswa
* **Lokasi Berkas:** `app/Http/Controllers/SubmissionController.php` method `store()`
* **Fitur Utama:**
  * Mengecek izin mahasiswa lewat `Gate::authorize('submit', $assignment)`.
  * Menolak pengumpulan jika batas waktu sudah habis dan dosen menyetel `allow_late = false`.
  * Menghitung status terlambat secara otomatis di server: `'is_late' => now()->gt($assignment->due_at)`.
  * Menggunakan `updateOrCreate` agar tugas lama ditimpa dan berkas lama di harddisk dibersihkan.

#### 4. Unduh Berkas Pengumpulan Tugas
* **Lokasi Berkas:** `app/Http/Controllers/SubmissionController.php` method `download()`
* **Fitur Utama:**
  * Menjalankan `Gate::authorize('view', $submission)`. Hanya pemilik tugas, dosen pengampu, dan admin yang bisa mengunduh.
  * Memastikan fisik berkas ada dengan `abort_unless(Storage::disk('local')->exists(...), 404)`.
  * Mengirimkan berkas ke browser dengan nama asli yang sopan menggunakan `Storage::disk('local')->download(...)`.

#### 5. Halaman Monitoring Pengumpulan Tugas Dosen
* **Lokasi Berkas:** `resources/views/assignments/submissions.blade.php`
* **Fitur Utama:** Dosen bisa melihat tabel seluruh mahasiswa di kelas, mengetahui siapa yang sudah dan belum mengumpulkan, melihat label keterlambatan, melihat ukuran berkas yang mudah dibaca menggunakan fungsi `Number::fileSize()`, dan mengunduh berkas tugas mahasiswa secara aman.

---

## 9.4 CHECKPOINT: Pertanyaan dan Jawaban Ujian

Berikut ringkasan jawaban cepat dan padat untuk sesi evaluasi dan ujian:

### 1. Kenapa submission tidak boleh disimpan di `storage/app/public`? Peragakan kebocorannya.
* **Jawaban:** Folder publik terhubung langsung ke web server lewat jalan pintas `public/storage`. Siapa pun yang tahu tautannya bisa mengunduh berkas tanpa perlu login dan tanpa pemeriksaan Policy.
* **Cara Peragakan:** Jika berkas ditaruh di folder publik, kita bisa menyalin alamat URL-nya, lalu membukanya di jendela Incognito atau terminal cURL tanpa login. Berkas langsung terunduh utuh tanpa ada halangan.

### 2. Kenapa nama berkas fisik dibuat acak? Serangan apa yang dicegah?
* **Jawaban:** Supaya tidak terjadi benturan nama jika ada dua mahasiswa yang mengunggah berkas dengan nama yang sama (`tugas.pdf`).
* **Serangan yang Dicegah:** Mencegah serangan *Path Traversal* (nama berkas jahat seperti `../../.env` yang bertujuan merusak berkas sistem server).

### 3. Apa beda validasi `mimes` dan `extensions`? Mana yang lebih aman dan kenapa?
* **Jawaban:** `extensions` hanya mencocokkan tulisan nama berkas di bagian belakang. `mimes` membaca struktur biner di dalam isi berkas (*magic bytes*).
* **Mana yang Lebih Aman:** `mimes` jauh lebih aman. Berkas virus PHP yang diganti namanya menjadi `laporan.pdf` akan lolos jika memakai `extensions`, tetapi akan langsung ditolak jika memakai `mimes` karena isi aslinya tetap berupa skrip teks PHP.

### 4. `max:5120` berarti berapa MB? Apa yang bisa membatalkannya di level server?
* **Jawaban:** `max:5120` bernilai **5 Megabyte (5 MB)** karena satuan di Laravel adalah Kilobyte (5120 dibagi 1024 = 5).
* **Yang Membatalkannya di Server:** Pengaturan `upload_max_filesize` dan `post_max_size` di berkas `php.ini`. Jika server PHP membatasi maksimal 2 MB, berkas 3 MB akan langsung ditolak sebelum sampai ke tangan Laravel.

### 5. Tunjukkan di kode Anda apa yang mencegah mahasiswa mengunduh submission mahasiswa lain.
* **Jawaban:** Di `SubmissionController@download`, baris pertama memanggil:
  ```php
  Gate::authorize('view', $submission);
  ```
  Lalu di `SubmissionPolicy@view`, ada aturan verifikasi:
  ```php
  return $user->role === 'admin'
      || $submission->assignment->course->lecturer_id === $user->id
      || $submission->user_id === $user->id;
  ```
  Kode ini memastikan bahwa pengunduh harus admin, dosen pengampu, atau mahasiswa pemilik berkas itu sendiri. Jika mahasiswa lain mencoba membuka tautan unduhannya, server langsung merespons dengan **403 Forbidden**.

### 6. Kenapa `is_late` dihitung di server, bukan dikirim dari browser?
* **Jawaban:** Karena jam di laptop pengguna bisa diubah dengan sangat mudah. Mahasiswa yang telat mengumpulkan tugas bisa memundurkan jam laptopnya sendiri sebelum mengklik kirim. Dengan menghitung waktu di server (`now()->gt($assignment->due_at)`), data waktu dijamin sah, adil, dan tidak bisa dimanipulasi.
