# Catatan Individu Minggu - 05 - Patra Ananda (10241061)

## 5. READ - BREAK - FIX - BUILD

### 5.1 READ — Penelusuran Alur Middleware, Otorisasi, dan Route Model Binding

Pembedahan alur request saat melewati lapisan keamanan dan routing pada Laravel 12:

1. **Titik Pendaftaran Middleware di Laravel 12:**
   Pada Laravel 12, arsitektur framework telah dipangkas sehingga berkas `app/Http/Kernel.php` ditiadakan. Seluruh konfigurasi middleware dipusatkan di `bootstrap/app.php` melalui method `->withMiddleware()`. Di sinilah alias kustom seperti `'role'` dipetakan ke class middleware `EnsureUserHasRole`.

2. **Siklus Request Melewati Middleware:**
   Saat request HTTP masuk (misalnya `GET /dosen/courses`), request melewati pipeline middleware global terlebih dahulu (seperti enkripsi cookies, session, start session), lalu masuk ke middleware rute:
   ```php
   $middleware->alias([
       'role' => \App\Http\Middleware\EnsureUserHasRole::class,
   ]);
   ```
   Di dalam middleware, sistem memeriksa apakah pengguna memiliki session login aktif dan apakah atribut `role` pada model `User` sesuai dengan parameter yang diminta (`dosen`). Jika gagal, middleware langsung memotong siklus hidup request dengan melempar exception `abort(403)` sebelum controller sempat tersentuh.

3. **Peran Route Model Binding (Implicit Binding):**
   Ketika parameter rute didefinisikan sebagai `Course $course`, Laravel secara otomatis melakukan pencarian `Course::where('id', $value)->firstOrFail()`. Jika ID tidak ditemukan di tabel database, Laravel langsung melempar `ModelNotFoundException` yang dikonversi menjadi HTTP respons `404 Not Found`.

4. **Keterbatasan Route Model Binding:**
   Route model binding hanya menjamin **keberadaan data di database**, bukan **hak akses pengguna**. Framework tidak tahu apakah user yang sedang login berhak melihat data tersebut. Oleh karena itu, pengecekan otorisasi (ownership check / Policy) wajib dilakukan secara eksplisit.

5. **Pentingnya Scoped Bindings pada Nested Routes:**
   Pada relasi bersarang seperti `/courses/{course}/assignments/{assignment}`, pemanggilan `Route::scopeBindings()` menginstruksikan Laravel untuk mengeksekusi pencarian dengan scoping relasi:
   ```php
   $course->assignments()->findOrFail($assignmentId);
   ```
   Hal ini mencegah celah di mana ID tugas milik mata kuliah A dapat diakses melalui URL mata kuliah B.

---

### 5.2 BREAK — Enam Kerusakan Otorisasi & Routing

Eksperimen pengujian kerusakan sengaja pada lapisan keamanan dan otorisasi.

---

##### BREAK 1: Hapus Ownership Check di `show` Mata Kuliah → Terjadi Celah IDOR
* **Yang Dirusak:** Menghapus baris `abort_unless($this->userCanView($course), 403);` pada method `CourseController::show()`.
* **Cara Coba:** 
  1. Login sebagai Dosen A (ID: 1).
  2. Buka URL mata kuliah milik Dosen B: `/dosen/courses/3`.
* **Yang Terjadi:** Dosen A dapat membuka, membaca materi, dan melihat konfigurasi mata kuliah yang diampu Dosen B tanpa batasan.
* **Kenapa Bahaya:** Ini adalah kerentanan **IDOR (*Insecure Direct Object Reference*)**. Pengguna dapat mengintip dan memanipulasi entitas milik pengguna lain hanya dengan mengganti nomor ID pada URL browser.
* **Solusi Benar:** Selalu lakukan pengecekan kepemilikan data sebelum mengembalikan view atau JSON:
  ```php
  abort_unless($this->userCanView($course), 403);
  ```

---

##### BREAK 2: Lupa Mendaftarkan Alias Middleware di `bootstrap/app.php`
* **Yang Dirusak:** Menghapus alias `'role'` dari `bootstrap/app.php`, sementara di `routes/web.php` route group tetap dipasangi `->middleware('role:dosen')`.
* **Cara Coba:** Akses halaman `/dosen/courses` melalui browser.
* **Yang Terjadi:** Sistem crash dengan pesan error:
  `ReflectionException: Class "role" does not exist` atau `BindingResolutionException`.
* **Kenapa Bahaya:** Aplikasi mengalami downtime fatal untuk seluruh rute yang bergantung pada middleware tersebut karena Laravel tidak dapat me-resolve string `'role'` dari Service Container.
* **Solusi Benar:** Pastikan setiap middleware string alias selalu didaftarkan pada closure `withMiddleware` di `bootstrap/app.php`.

---

##### BREAK 3: Hapus `Route::scopeBindings()` pada Nested Resource Tugas
* **Yang Dirusak:** Menghapus pembungkus `Route::scopeBindings()->group(...)` pada resource `courses.assignments`.
* **Cara Coba Singkat:**
  Akses URL dengan pasangan yang sengaja disilangkan:
  `/courses/1/assignments/99` (di mana tugas ID 99 sebenarnya milik mata kuliah Course ID 2).
* **Yang Terjadi:** Halaman tetap berhasil terbuka (200 OK) dan menampilkan detail tugas 99 di bawah konteks Course 1.
* **Kenapa Bahaya:** Terjadi inkonsistensi data relasional dan potensi kebocoran data akademik antar kelas (*cross-course data leakage*).
* **Solusi Benar:** Selalu bungkus rute bertingkat (*nested routes*) dengan `Route::scopeBindings()` atau panggil method `->scopeBindings()` pada route resource.

---

##### BREAK 4: Hanya Mengandalkan Middleware `role:dosen` Tanpa Cek Kepemilikan Model
* **Yang Dirusak:** Menganggap rute sudah aman hanya karena dibungkus `middleware('role:dosen')`, lalu membiarkan method update/delete menerima ID apa saja tanpa verifikasi dosen pengampu.
* **Cara Coba Singkat:**
  Mengirim request edit/hapus data tugas mata kuliah dosen lain:
  ```bash
  curl -X DELETE http://127.0.0.1:8000/dosen/courses/5 \
    -H "Accept: application/json" \
    --cookie "kampuslms_session=SESSION_DOSEN_A"
  ```
* **Yang Terjadi:** Data mata kuliah Dosen B berhasil terhapus oleh Dosen A karena keduanya sama-sama memiliki role `dosen`.
* **Kenapa Bahaya:** *Horizontal Privilege Escalation*. Middleware hanya memvalidasi tipe peran (Role-Based), bukan kepemilikan objek (Object-Level Authorization).
* **Solusi Benar:** Selalu kombinasikan middleware role pada gerbang rute dengan pengecekan kepemilikan spesifik pada controller atau Policy (`$course->lecturer_id === auth()->id()`).

---

##### BREAK 5: Menyamarkan ID dengan UUID Tanpa Proteksi Otorisasi di Server
* **Yang Dirusak:** Mengganti kolom ID numerik menjadi format UUID acak pada URL (`/assignments/9b1deb4d-...`), lalu menghapus pengecekan otorisasi di controller dengan asumsi URL tidak bisa ditebak.
* **Cara Coba:** Bagikan tautan tugas berformat UUID ke mahasiswa dari jurusan/kelas lain yang tidak terdaftar.
* **Yang Terjadi:** Mahasiswa luar tetap dapat membuka dan mengunduh soal tugas tersebut secara langsung.
* **Kenapa Bahaya:** Menerapkan *security through obscurity*. UUID hanya mempersulit tebak-tebakan nomor urut (*anti-enumeration*), tetapi tidak mengamankan pintu akses sama sekali jika tautannya tersebar.
* **Solusi Benar:** Keamanan sejati terletak pada validasi relasi pengguna di sisi server, bukan pada format string penanda identitas.

---

##### BREAK 6: Akses ID Fiktif Tanpa Halaman Error 404 yang Terstandarisasi
* **Yang Dirusak:** Mengakses model binding yang tidak terdaftar di database saat view kustom error belum dipersiapkan.
* **Cara Coba:** Buka URL acak seperti `/courses/999999`.
* **Yang Terjadi:** Laravel menangkap kegagalan pencarian model dan memicu `404 Not Found`. Namun jika view tidak dikelola, pengguna disuguhi halaman default framework yang kaku.
* **Kenapa Bahaya:** Pesan error default yang tidak seragam menurunkan kredibilitas aplikasi dan berisiko menampilkan informasi teknis yang tidak diperlukan ke publik.
* **Solusi Benar:** Sediakan template terpadu di `resources/views/errors/404.blade.php` dan `resources/views/errors/403.blade.php` yang terintegrasi dengan layout sistem.

---

### 5.3 FIX — Perbaikan Masalah Otorisasi & Routing Branch W05

Pada latihan eksplorasi branch `W05` ditemukan 5 kerentanan dan masalah arsitektur:

1. **Celah IDOR pada aksi resource controller** — method `show`, `edit`, dan `update` membaca model secara langsung dari parameter tanpa memverifikasi hak kepemilikan akun.
2. **Ketiadaan filter role pada route group** — rute khusus staf dan pengajar dapat diakses langsung oleh role lain karena tidak ada middleware penjaga gerbang.
3. **Pendaftaran middleware salah format** — registrasi middleware masih mencoba mengimpor berkas lama `Kernel.php` yang sudah tidak digunakan pada struktur Laravel 12.
4. **Nested route tidak terisolasi (Unscoped Bindings)** — sub-resource tugas dan materi dapat dimuat silang dengan ID mata kuliah yang tidak cocok.
5. **Daftar index tidak tersaring berdasarkan peran** — semua pengguna melihat seluruh data secara global alih-alih data yang relevan dengan hak akses masing-masing (dosen hanya melihat kelasnya, mahasiswa hanya melihat kelas yang diambil).

---


