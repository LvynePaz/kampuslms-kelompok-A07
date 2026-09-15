# Catatan Individu Minggu - 03 - Patra Ananda (10241061)


## 3. READ - BREAK - FIX - BUILD 

3.1 READ 

1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.

<img src = "images/ERD.jpeg" width = 600>


2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.

|Foreign key |``onDelete``|Alasan|
|------------|------------|------|
|``courses.lecturer_id``|``SETNULL``|Mata kuliah tetap penting sebagai data akademik meskipun dosennya dihapus. Kolom harus nullable.|
``assignments.course_id``|``CASCADE``|Tugas tidak bermakna tanpa mata kuliah induknya. Jika mata kuliah dihapus, tugasnya ikut dihapus.|
``submissions.assignment_id``|``CASCADE``|Submission hanya ada karena tugas tertentu. Jika tugas dihapus, submission terkait juga harus dihapus.|
``submissions.student_id``|``RESTRICT``|Data submission dan histori akademik tidak boleh hilang hanya karena akun mahasiswa dihapus. Lebih aman menonaktifkan akun atau melakukan soft delete.
``grades.submission_id``|``CASCADE``|Nilai tidak bermakna tanpa submission. Jika submission dihapus, nilai terkait ikut dihapus.

3. Mata kuliahnya tetap ada, tetapi ``courses.lecturer_id`` diubah menjadi ``NULL``.

Alasannya, dosen dan mata kuliah adalah entitas yang berbeda. Penghapusan dosen tidak berarti mata kuliah, tugas, submission, dan histori nilai harus ikut hilang. Mata kuliah tersebut nantinya dapat diberikan kepada dosen pengganti.

4. Karena satu submission hanya boleh memiliki satu nilai.

``index`` biasa hanya mempercepat pencarian, tetapi masih mengizinkan data seperti:

submission_id | score
--------------|------
10            | 80
10            | 90

Dengan ``unique``, database mencegah submission yang sama diberi dua nilai:

$table->foreignId('submission_id')
    ->unique()
    ->constrained()
    ->cascadeOnDelete();

Dengan demikian, relasi ``submissions`` ke ``grades`` adalah one-to-zero-or-one: sebuah submission boleh belum dinilai, tetapi setelah dinilai hanya boleh memiliki satu record nilai.


3.2 BREAK — Eksplorasi 5 Kerusakan Database & Keamanan (Hands-on)

Berikut adalah rangkuman 5 simulasi pengujian kerusakan yang telah dicoba langsung di terminal dan Tinker. Format dibuat ringkas dan langsung pada intinya (*to-the-point*) agar mudah dipahami dan siap dipertanggungjawabkan saat sesi wawancara.

---

##### BREAK 1: Hapus `unique(['course_id', 'user_id'])` di Tabel Pivot → Mahasiswa Bisa Daftar Mata Kuliah yang Sama Berkali-kali

* **Yang Dirusak:** Constraint `unique` pada tabel pivot `course_user` dihapus, sehingga database tidak lagi mencegah satu mahasiswa terdaftar ke mata kuliah yang sama lebih dari satu kali.
* **Cara Coba Singkat:**
  1. **Buka file:** `database/migrations/xxxx_create_course_user_table.php`
  2. **Komentari baris berikut** (tambah `//` di depannya):
     ```php
     // $table->unique(['course_id', 'user_id']); // <-- beri // di sini
     ```
  3. **Di terminal**, jalankan ulang semua migrasi dari awal:
     ```bash
     php artisan migrate:fresh
     ```
  4. **Buka Tinker** (shell interaktif Laravel):
     ```bash
     php artisan tinker
     ```
  5. **Di dalam Tinker**, jalankan ketiga baris ini satu per satu — tekan Enter setelah masing-masing:
     ```php
     DB::table('course_user')->insert(['course_id' => 1, 'user_id' => 1]);
     DB::table('course_user')->insert(['course_id' => 1, 'user_id' => 1]);
     DB::table('course_user')->where('user_id', 1)->count();
     ```
  6. **Ketik `exit` lalu Enter** untuk keluar dari Tinker.
* **Yang Terjadi di terminal:** Output menghasilkan angka **`2`**. Database menerima kedua data tanpa error.
* **Kenapa Bahaya:** Validasi di controller saja tidak cukup — saat terjadi *race condition* (misalnya tombol daftar diklik dua kali berturut-turut), kedua request bisa lolos secara bersamaan sebelum pengecekan selesai. Hasilnya, satu mahasiswa bisa terdaftar dua kali dan muncul dua kali di daftar nilai. Constraint di level database adalah lapisan validasi terakhir yang tidak bisa dilewati dari sisi aplikasi.
* **Solusi Benar:** Tambahkan `$table->unique(['course_id', 'user_id']);` di migrasi tabel pivot agar database menolak data duplikat di level storage, bukan hanya di level aplikasi.

---

##### BREAK 2: Bocorkan `role` ke dalam `$fillable` Model User → Siapa Pun Bisa Naik Pangkat Jadi Admin Lewat Form

* **Yang Dirusak:** Kolom `role` ditambahkan ke `$fillable`, sehingga nilainya bisa diisi langsung dari input request luar tanpa pembatasan (*Mass Assignment*).
* **Cara Coba Singkat:**
  1. **Buka file:** `app/Models/User.php`
  2. **Cari array `$fillable`**, lalu **tambahkan `'role'`** di dalamnya sehingga menjadi:
     ```php
     protected $fillable = [
         'name',
         'email',
         'password',
         'role', // <-- tambahkan baris ini
     ];
     ```
  3. **Simpan file** (`Ctrl+S`).
  4. **Buka Tinker** di terminal:
     ```bash
     php artisan tinker
     ```
  5. **Di dalam Tinker**, jalankan blok kode ini (copy-paste sekaligus, lalu tekan Enter):
     ```php
     $user = App\Models\User::create([
         'name' => 'Penyusup',
         'email' => 'hacker@test.com',
         'password' => bcrypt('123'),
         'role' => 'admin', // <-- diselipkan, padahal tidak ada di form HTML
     ]);
     echo $user->role;
     ```
  6. **Ketik `exit` lalu Enter** untuk keluar dari Tinker.
* **Yang Terjadi di terminal:** Output mengembalikan nilai **`"admin"`**. Artinya, kolom `role` berhasil diisi dari luar tanpa melewati validasi khusus.
* **Kenapa Bahaya:** Tampilan form di browser tidak menjamin keamanan. Siapa pun bisa mengirim request langsung via Postman, cURL, atau Inspect Element dengan menambahkan field `role=admin`. Token CSRF hanya memverifikasi asal request, bukan isi datanya. Jika kolom `role` tidak diproteksi di model, siapa pun bisa mengubah peran akun melalui request HTTP biasa.
* **Solusi Benar:** Jangan masukkan kolom sensitif seperti `role`, `is_admin`, atau `score` ke dalam `$fillable`. Kolom `role` harus diisi secara eksplisit di controller (`$user->role = 'mahasiswa';`), bukan diambil mentah dari input request.

---

##### BREAK 3: Pakai `protected $guarded = []` → Semua Kolom Tabel Terbuka Bebas untuk Diisi dari Luar

* **Yang Dirusak:** Properti `$fillable` dihapus dan diganti dengan `$guarded = []` (array kosong), yang berarti tidak ada satu pun kolom yang diblokir dari pengisian via mass assignment.
* **Cara Coba Singkat:**
  1. **Buka file:** `app/Models/User.php`
  2. **Hapus seluruh baris** `protected $fillable = [...]`, lalu **ganti** dengan satu baris berikut:
     ```php
     protected $guarded = []; // <-- blacklist dikosongkan = semua kolom terbuka
     ```
  3. **Simpan file** (`Ctrl+S`).
  4. **Buka Tinker** di terminal:
     ```bash
     php artisan tinker
     ```
  5. **Di dalam Tinker**, jalankan kode berikut (copy-paste sekaligus):
     ```php
     $user = App\Models\User::create([
         'name' => 'Penyusup',
         'email' => 'hacker2@test.com',
         'password' => bcrypt('123'),
         'role' => 'admin', // <-- kolom sensitif bebas masuk
     ]);
     echo $user->role;
     ```
  6. **Ketik `exit` lalu Enter** untuk keluar dari Tinker.
* **Yang Terjadi di terminal:** Output tetap menghasilkan **`"admin"`**. Kolom `role` tetap bisa diisi dari luar meskipun tidak ada di form.
* **Kenapa Bahaya:** `$guarded = []` berarti semua kolom tabel bisa diisi via mass assignment tanpa terkecuali. Jika ada kolom baru ditambahkan ke tabel (misalnya `api_token`, `status_aktif`, atau `saldo`), kolom tersebut secara otomatis ikut terbuka tanpa perlu konfigurasi tambahan.
* **Solusi Benar:** Gunakan `$fillable` dan daftarkan secara eksplisit hanya kolom yang memang boleh diisi dari input pengguna. Hindari `$guarded = []` karena pendekatan ini rentan saat skema tabel berkembang.

---

##### BREAK 4: Kosongkan Method `down()` pada Migrasi → Rollback Gagal Total, Pipeline CI/CD Langsung Error

* **Yang Dirusak:** Isi fungsi `down()` dihapus, sehingga proses rollback migrasi tidak melakukan apa-apa — tabel yang sudah dibuat tidak akan dihapus saat rollback dijalankan.
* **Cara Coba Singkat:**
  1. **Pastikan migrasi sudah jalan normal dulu.** Jalankan ini di terminal:
     ```bash
     php artisan migrate:fresh
     ```
  2. **Buka file:** `database/migrations/xxxx_create_users_table.php`
  3. **Cari fungsi `down()`**, lalu **kosongkan isinya** sehingga menjadi:
     ```php
     public function down(): void
     {
         // Schema::dropIfExists('users'); <-- baris ini dihapus / dikosongkan
     }
     ```
  4. **Simpan file** (`Ctrl+S`).
  5. **Kembali ke terminal**, lalu jalankan perintah refresh migrasi:
     ```bash
     php artisan migrate:refresh
     ```
* **Yang Terjadi di terminal:** Terminal langsung tampilkan error merah:  
  `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'users' already exists`
* **Kenapa Bahaya:** Perintah `migrate:refresh` bekerja dengan urutan: jalankan `down()` untuk menghapus semua tabel, lalu jalankan ulang `up()` untuk membuatnya kembali. Jika `down()` kosong, tabel lama tidak dihapus. Saat `up()` mencoba membuat tabel yang sama, database akan mengembalikan error karena tabel sudah ada. Akibatnya, pipeline **CI/CD (misalnya GitHub Actions) akan gagal** dan proses deploy terhenti.
* **Solusi Benar:** Setiap tabel yang dibuat di `up()` dengan `Schema::create(...)` harus ada pasangannya di `down()` berupa `Schema::dropIfExists(...)`. Keduanya harus selalu simetris.

---

##### BREAK 5: Ganti `restrictOnDelete` → `cascadeOnDelete` pada Relasi Dosen → Mata Kuliah Ikut Terhapus Saat Akun Dosen Dihapus

* **Yang Dirusak:** Perilaku foreign key pada kolom `lecturer_id` diubah dari `nullOnDelete` menjadi `cascadeOnDelete`, sehingga ketika data dosen dihapus, semua mata kuliah yang mengacu ke dosen tersebut ikut terhapus secara otomatis.
* **Cara Coba Singkat:**
  1. **Buka file:** `database/migrations/xxxx_create_courses_table.php`
  2. **Cari definisi kolom `lecturer_id`**, lalu **ubah `nullOnDelete()`/`restrictOnDelete()`** menjadi `cascadeOnDelete()`:
     ```php
     // SEBELUM (benar):
     $table->foreignId('lecturer_id')->nullable()->constrained('users')->nullOnDelete();

     // SESUDAH (sengaja dirusak):
     $table->foreignId('lecturer_id')->nullable()->constrained('users')->cascadeOnDelete();
     ```
  3. **Simpan file** (`Ctrl+S`).
  4. **Di terminal**, jalankan ulang semua migrasi dari awal:
     ```bash
     php artisan migrate:fresh
     ```
  5. **Buka Tinker:**
     ```bash
     php artisan tinker
     ```
  6. **Di dalam Tinker**, jalankan baris-baris ini satu per satu — tekan Enter setelah masing-masing:
     ```php
     // Buat akun dosen baru
     $dosen = App\Models\User::create(['name' => 'Dosen A', 'email' => 'dosen@test.com', 'password' => bcrypt('123'), 'role' => 'dosen']);

     // Buat mata kuliah yang mengacu ke dosen tersebut
     DB::table('courses')->insert(['code' => 'SI101', 'name' => 'Pemrograman Web', 'sks' => 3, 'lecturer_id' => $dosen->id]);

     // Hapus akun dosen
     $dosen->delete();

     // Cek apakah mata kuliah masih ada (harusnya ada, tapi ternyata...)
     DB::table('courses')->where('code', 'SI101')->first();
     ```
  7. **Ketik `exit` lalu Enter** untuk keluar dari Tinker.
* **Yang Terjadi di terminal:** Output mengembalikan **`null`**. Data mata kuliah "Pemrograman Web" terhapus dari database bersamaan dengan dihapusnya akun dosen.
* **Kenapa Bahaya:** Jika akun dosen dihapus (karena pensiun, resign, atau alasan lain), semua mata kuliah yang diajarnya ikut hilang, termasuk tugas, materi, dan riwayat nilai mahasiswa yang terkait. Padahal mata kuliah tersebut seharusnya tetap ada dan bisa dialihkan ke dosen lain.
* **Solusi Benar:** Gunakan `nullOnDelete()` pada kolom `lecturer_id`. Dengan cara ini, ketika akun dosen dihapus, nilai `lecturer_id` di tabel `courses` cukup diubah menjadi `NULL` — mata kuliah tetap ada dan dapat ditetapkan ke dosen pengganti.


