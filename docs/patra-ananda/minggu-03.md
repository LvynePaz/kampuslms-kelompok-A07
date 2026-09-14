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

##### BREAK 1: Hapus `unique(['course_id', 'user_id'])` di Tabel Pivot

* **Yang Dirusak:** Gembok database dimatikan, jadi tidak ada lagi yang melarang mahasiswa daftar ke mata kuliah yang sama lebih dari satu kali.
* **Cara Coba Singkat:**
  1. Di migrasi `create_course_user_table.php`, beri `//` pada `$table->unique(['course_id', 'user_id']);`.
  2. Jalankan `php artisan migrate:fresh` lalu buka `php artisan tinker`.
  3. Daftarkan mahasiswa yang sama ke mata kuliah yang sama dua kali:
     ```php
     DB::table('course_user')->insert(['course_id' => 1, 'user_id' => 1]);
     DB::table('course_user')->insert(['course_id' => 1, 'user_id' => 1]);
     DB::table('course_user')->where('user_id', 1)->count();
     ```
* **Yang Terjadi di terminal:** Output menghasilkan angka **`2`**. Database menerima kedua data tanpa error.
* **Kenapa Bahaya:** Kalau cuma mengandalkan pengecekan di controller, itu gampang jebol pas ada *race condition* (misalnya mahasiswa tidak sabar lalu spam klik tombol daftar 2x saat sinyal lemot). Akhirnya satu mahasiswa bisa terdaftar dobel dan dapat dua lembar nilai. Database harus jadi benteng terakhir yang menolak duplikasi.
* **Solusi Benar:** Wajib pasang `$table->unique(['course_id', 'user_id']);` di migrasi tabel pivot.

---

##### BREAK 2: Bocorkan `role` ke dalam `$fillable` Model User

* **Yang Dirusak:** Kolom penentu jabatan (`role`) dibocorkan ke `$fillable`, jadi siapa pun bisa mengisi hak akses sesuka hati dari luar (*Mass Assignment*).
* **Cara Coba Singkat:**
  1. Di `app/Models/User.php`, tambahkan `'role'` ke dalam array `$fillable`.
  2. Buka `php artisan tinker` dan simulasikan request form yang diselipi atribut `role`:
     ```php
     $user = App\Models\User::create([
         'name' => 'Penyusup',
         'email' => 'hacker@test.com',
         'password' => '123',
         'role' => 'admin' // <-- diselipkan padahal tidak ada di form HTML
     ]);
     $user->role;
     ```
* **Yang Terjadi di terminal:** Output mengembalikan nilai **`"admin"`**. Pengguna biasa berhasil naik pangkat sendiri jadi administrator kampus.
* **Kenapa Bahaya:** Tampilan form di browser itu bukan jaminan keamanan sama sekali. Pengguna tinggal buka Inspect Element atau tembak lewat Postman/cURL buat menyelipkan `'role' => 'admin'`. Token CSRF pun tidak peduli isinya apa, dia cuma ngecek asal website. Kalau tidak dijaga di model, orang luar bisa langsung jadi admin kampus dalam hitungan detik.
* **Solusi Benar:** Jangan pernah masukkan kolom sensitif (`role`, `is_admin`, `score`) ke `$fillable`. Kolom `role` harus dikunci dan diisi manual lewat kode controller (`$user->role = 'mahasiswa';`), bukan ditelan mentah-mentah dari input form.

---

##### BREAK 3: Pakai Jalan Pintas `protected $guarded = [];`

* **Yang Dirusak:** Semua satpam pelindung model dimatikan total pakai jalan pintas `$guarded = []`.
* **Cara Coba Singkat:**
  1. Di `app/Models/User.php`, hapus `$fillable` dan ganti jadi `protected $guarded = [];`.
  2. Di Tinker, buat user baru dengan menyelipkan parameter sembarang apa saja (misal: `'role' => 'admin'`).
* **Yang Terjadi di terminal:** Output tetap tembus menjadi **`"admin"`**.
* **Kenapa Bahaya:** Ini kebiasaan buruk demi cepat selesai. Karena blacklist-nya kosong,keamanan di database jadi terbuka lebar untuk kolom apa pun. Bahayanya lagi, kalau nanti ada teman kelompok yang nambah kolom baru di tabel (seperti `saldo`, `api_token`, atau `status_aktif`), kolom baru itu otomatis langsung bisa diisi bebas oleh siapa pun.
* **Solusi Benar:** Jangan pernah pakai `$guarded = []`. Wajib pakai `$fillable` dan daftarkan satu per satu kolom mana saja yang memang boleh diisi oleh pengguna umum.

---

##### BREAK 4: Kosongkan Method `down()` pada Migrasi

* **Yang Dirusak:** Fungsi pembatalan/penghapusan (`down()`) dikosongkan, jadi migrasi cuma bisa maju tapi tidak bisa mundur (*tidak reversible*).
* **Cara Coba Singkat:**
  1. Di berkas `create_users_table.php`, kosongkan isi fungsi `down()`:
     ```php
     public function down(): void {}
     ```
  2. Di terminal jalankan perintah refresh migrasi:
     ```bash
     php artisan migrate:refresh
     ```
* **Yang Terjadi di terminal:** Terminal langsung tampilkan error merah:  
  `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'users' already exists`
* **Kenapa Bahaya:** Perintah `migrate:refresh` itu alurnya: hapus dulu semua tabel lewat `down()`, baru bikin ulang lewat `up()`. Karena fungsi `down()`-nya kosong, tabel lamanya masih nongkrong di database. Pas `up()` mau jalan lagi, database langsung error karena tabelnya sudah ada. Efek fatalnya, pipeline otomatis **CI/CD (GitHub Actions) langsung gagal/merah** dan proses deploy ke server rusak.
* **Solusi Benar:** Apa pun tabel yang dibuat di fungsi `up()` (misal `Schema::create`), wajib ada perintah buat menghapusnya di fungsi `down()` (misal `Schema::dropIfExists`).

---

##### BREAK 5: Ganti `restrictOnDelete` Jadi `cascadeOnDelete` pada Relasi Dosen

* **Yang Dirusak:** Aturan hapus data diubah. Jadi kalau akun dosen dihapus, mata kuliah yang dia ajar bakal ikut terhapus otomatis.
* **Cara Coba Singkat:**
  1. Di migrasi `courses`, ubah kolom `lecturer_id` menjadi `cascadeOnDelete()`.
  2. Jalankan `php artisan migrate:fresh` dan buka `php artisan tinker`.
  3. Buat dosen dan mata kuliah, lalu hapus dosen tersebut:
     ```php
     $dosen = App\Models\User::create(['name' => 'Dosen A', 'email' => 'dosen@test.com', 'password' => '123', 'role' => 'dosen']);
     DB::table('courses')->insert(['code' => 'SI101', 'name' => 'Pemrograman Web', 'sks' => 3, 'lecturer_id' => $dosen->id]);

     // Hapus dosen
     $dosen->delete();

     // Cek apakah mata kuliah masih ada
     DB::table('courses')->where('code', 'SI101')->first();
     ```
* **Yang Terjadi di terminal:** Output jadi **`null`** (kosong). Mata kuliah "Pemrograman Web" langsung hilang dari database.
* **Kenapa Bahaya:** Kalau ada dosen pensiun, resign, atau akunnya dihapus admin, masa mata kuliahnya ikut hilang? Nanti tugas, materi, dan riwayat nilai mahasiswa di mata kuliah itu bakal ikut terhapus semua. Harusnya mata kuliah tetap aman di sistem, tinggal diganti ke dosen lain yang baru.
* **Solusi Benar:** Tetap pakai `restrictOnDelete`. Jadi kalau akun dosen mau dihapus tapi dia masih punya mata kuliah yang diajar, database bakal menolak sampai mata kuliahnya dipindahkan dulu ke dosen pengganti.


