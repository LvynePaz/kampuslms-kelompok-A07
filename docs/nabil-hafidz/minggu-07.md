# Catatan Individu Minggu - 07 - Nabil Hafidz (10241055)

## 7.3 Read → Break → Fix → Build

---

### READ — Bedah Starter Kit & Mekanisme File Upload / Autentikasi (30 menit)

#### 1. Berkas mana yang menangani POST login? Method apa?
* **Berkas:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
* **Method:** `store(LoginRequest $request)`
* **Rute:** `Route::post('login', [AuthenticatedSessionController::class, 'store'])` di dalam `routes/auth.php`.
* **Penjelasan:** Request submit form login diarahkan ke method `store()`. Di sini request divalidasi dan diotentikasi melalui form request `LoginRequest`, kemudian session di-regenerate sebelum redirect ke dashboard.

---

#### 2. Di baris mana Auth::attempt() atau setaranya dipanggil?
* **Berkas:** `app/Http/Requests/Auth/LoginRequest.php`
* **Method:** `authenticate()`
* **Kodenya:**
  ```php
  if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
      RateLimiter::hit($this->throttleKey());

      throw ValidationException::withMessages([
          'email' => trans('auth.failed'),
      ]);
  }
  ```
* **Penjelasan:** Fungsi `Auth::attempt()` mencocokkan kredensial email dan password plaintext (yang di-hash secara otomatis menggunakan bcrypt) dengan tabel `users`. Jika gagal, penghitung rate limiter dinaikkan untuk mencegah *brute-force attack*.

---

#### 3. Temukan session()->regenerate(). Kenapa ia ada di situ?
* **Berkas:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
* **Kodenya:**
  ```php
  $request->authenticate();
  $request->session()->regenerate();
  ```
* **Kenapa ia ada di situ:** Perintah ini mencegah serangan **Session Fixation**. Tanpa regenerasi session, penyerang dapat menyuntikkan ID session tertentu sebelum korban login dan tetap menggunakan session yang sama setelah korban terautentikasi.

---

#### 4. Di mana kata sandi di-hash? Cari cast hashed di model User.
* **Berkas:** `app/Models/User.php`
* **Kodenya:**
  ```php
  protected function casts(): array
  {
      return [
          'email_verified_at' => 'datetime',
          'password' => 'hashed',
      ];
  }
  ```
* **Penjelasan:** Cast `'password' => 'hashed'` memastikan nilai plaintext yang di-assign ke atribut `password` selalu di-hash menggunakan algoritma default Laravel (bcrypt/argon2) secara transparan sebelum disimpan ke database.

---

#### 5. DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.
* **Hasil:**
  * Sebelum login: Cookie `laravel_session` bernilai string acak tamu.
  * Sesudah login: Nilai string `laravel_session` berubah total menjadi ID sesi baru yang diterbitkan server.
* **Kesimpulan:** Menunjukkan bahwa `session()->regenerate()` berhasil memperbarui identitas sesi pengguna di sisi client.

---

#### 6. Logout, lalu tekan tombol back. Apa yang terjadi? Kenapa?
* **Apa yang terjadi:** Browser menampilkan halaman dashboard sebelumnya sesaat dari cache lokal (BFCache).
* **Kenapa:** Halaman tersebut dimuat dari memori cache browser, bukan dari request baru ke server. Begitu halaman di-refresh atau tautan diklik, server langsung merespons redirect ke halaman login karena sesi di server sudah dihancurkan (`Auth::logout()` dan `session()->invalidate()`).

---

### BREAK — Delapan Kerusakan & Eksperimen Keamanan Berkas (50 menit)

| # | Yang Dicoba | Perilaku yang Terjadi | Penjelasan & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Hapus `session()->regenerate()` dari `AuthenticatedSessionController`. | Login tetap sukses, tetapi ID cookie session di browser tidak pernah berganti. | **Session Fixation:** Penyerang bisa membajak sesi jika mengetahui ID sesi pra-login korban. |
| **2** | Hapus pengecekan Policy `Gate::authorize('download', $material)` pada pengunduhan materi. | Mahasiswa dari luar kelas atau tamu yang menebak ID materi langsung bisa mengunduh file privat. | **Celah IDOR (Insecure Direct Object Reference):** Mengakibatkan kebocoran dokumen kuliah privat/rahasia ke pihak yang tidak berhak. |
| **3** | Simpan berkas materi di disk `public` alih-alih `local` (privat). | Berkas dapat diakses langsung oleh siapa saja via URL `http://domain/storage/materials/file.pdf` tanpa login dan tanpa melewati controller/policy. | **Bypass Otorisasi:** File di storage publik dapat di-crawl atau diakses langsung lewat web server (Nginx/Apache) tanpa kontrol aplikasi Laravel. |
| **4** | Hapus validasi tipe MIME/ekstensi (`mimes:pdf,pptx,docx`). | Pengguna bisa mengunggah file berbahaya seperti `.exe`, `.php`, atau shell script. | **Remote Code Execution (RCE) & Malicious Upload:** Pengunggah dapat menyisipkan script berbahaya yang bisa dieksekusi server. |
| **5** | Naikkan limit upload melebihi kapasitas server atau tanpa validasi `max:20480`. | Server berisiko mengalami kehabisan disk/RAM saat menerima berkas sangat besar. | **Denial of Service (DoS):** Penyerang dapat membanjiri ruang penyimpanan server dengan mengunggah berkas raksasa secara massal. |
| **6** | Hapus event penghapusan berkas fisik saat record materi dihapus. | Record di database hilang, tetapi file fisik tetap tertinggal di folder `storage/app/private`. | **Orphaned Files (Storage Leak):** Membuang kapasitas disk server secara sia-sia dari waktu ke waktu (*resource exhaustion*). |
| **7** | Sisipkan karakter path traversal `../../` pada original filename. | Tanpa sanitasi, file bisa tertulis ke direktori di luar target penyimpanan yang ditentukan. | **Arbitrary File Write / Path Traversal:** Laravel mengamankan ini dengan menggunakan hashing acak untuk path penyimpanan fisik. |
| **8** | Akses endpoint unduh materi milik dosen B menggunakan akun Dosen A yang tidak mengajar mata kuliah tersebut. | Server langsung mengembalikan respons **HTTP 403 Forbidden**. | **Proteksi Policy Berjalan:** Dosen lain yang tidak mengampu mata kuliah terkait tidak diizinkan mengakses berkas materi. |

---

### BUILD — Fitur Materi Perkuliahan (Tugas Poin 1 & 2 - Nabil Hafidz)

Sesuai pembagian tugas, berikut implementasi fitur materi yang telah diselesaikan:

#### 1. Unggah Materi oleh Dosen (Poin 1)
* **Validasi Ketat Format & Ukuran:**
  * Ekstensi & MIME yang diizinkan: **PDF, PPTX, dan DOCX**.
  * Batas ukuran berkas: **Maksimal 20 MB** (`max:20480` KB).
  * Pesan validasi berbahasa Indonesia yang jelas untuk setiap kegagalan input.
* **Penyimpanan Disk Privat (Private Storage):**
  * Berkas materi fisik disimpan pada disk privat `local` (`storage/app/private/materials/`), bukan di folder publik.
  * Penambahan konfigurasi disk alias `'private'` di `config/filesystems.php`.
  * Berkas **TIDAK BISA** diakses langsung melalui URL publik atau web server, melainkan wajib melalui otorisasi Controller & Policy.
* **Alternatif Tautan Eksternal:**
  * Dosen dapat memilih tipe `link` untuk menyematkan tautan materi eksternal (Google Drive, YouTube, website referensi).
  * Tautan dinormalisasi secara otomatis (menambahkan skema `https://` jika belum ada).

#### 2. Unduh Materi via Controller + Policy (Poin 2)
* **Otorisasi Terpusat (`MaterialPolicy`):**
  * Method `download()` dan `view()` pada `MaterialPolicy` memverifikasi bahwa pengguna yang meminta berkas adalah:
    1. **Admin**, ATAU
    2. **Dosen Pengampu** mata kuliah tersebut (`$material->course->lecturer_id === $user->id`), ATAU
    3. **Mahasiswa yang Terdaftar** di mata kuliah tersebut (`$material->course->students()->whereKey($user->id)->exists()`).
  * Mahasiswa yang tidak terdaftar otomatis menerima respons **HTTP 403 Forbidden**.
  * Pengguna yang belum login diarahkan ke halaman login (**HTTP 302 Redirect**).
* **Controller Responsif (`MaterialController`):**
  * Method `download()`: Mengembalikan file dengan header `Content-Disposition: attachment` menggunakan `Storage::disk('local')->download()`.
  * Method `showFile()`: Mendukung pratinjau inline (`response()`) serta pengunduhan paksa jika parameter `?download=1` disertakan.
  * Rute terdaftar: `GET /materials/{material}/download` (`materials.download`) dan `GET /materials/{material}/file` (`materials.file`).

#### 3. Penanganan Pembersihan Berkas Fisik & Kasus Cascade
* **Pembersihan Berkas Fisik:**
  * Pada `MaterialController::destroy()` dan hook Eloquent `Material::booted() static::deleting()`, berkas fisik di storage privat (`local`) maupun publik secara otomatis dihapus (`Storage::disk(...)->delete()`).
* **Kasus Cascade:**
  * Pada model `Course::booted() static::deleting()`, setiap materi di bawah mata kuliah yang dihapus dieksekusi penghapusannya secara rekursif sehingga berkas fisik tidak ada yang tertinggal (*no orphaned files*).

#### 4. Tampilan `original_name` dan Ukuran Berkas Terbaca Manusia
* **Accessor Eloquent `formatted_file_size`:**
  * Menghitung byte menjadi unit yang mudah dibaca pengguna: `B`, `KB`, `MB`, `GB` (contoh: `14.65 MB`, `512 KB`).
* **Pembaruan Tampilan Blade:**
  * Halaman Detail Mata Kuliah (`resources/views/courses/show.blade.php`): Menampilkan nama berkas asli (`original_name`), ukuran berkas terformat, serta tombol **Unduh** dan **Buka**.
  * Halaman Detail Materi (`resources/views/materials/show.blade.php`): Menampilkan metadata berkas lengkap dan tombol aksi unduh privat.
  * Form Edit & Tambah Materi (`resources/views/materials/create.blade.php` & `edit.blade.php`): Menyediakan input berkas dengan batasan `accept=".pdf,.pptx,.docx"` dan petunjuk batas 20 MB.

---

### Pengujian Otomatis (Automated Test Suite)

Fitur materi telah diuji secara menyeluruh melalui test suite baru `tests/Feature/MaterialFeatureTest.php` dengan **10 skenario uji (46 assertions) lulus 100%**:

1. `test_dosen_can_upload_pdf_material_to_private_disk_up_to_20mb` — Memastikan file PDF ~15 MB berhasil diunggah ke disk privat `local` dan metadata tersimpan.
2. `test_dosen_can_upload_pptx_and_docx_materials` — Memverifikasi format PPTX dan DOCX diterima oleh validasi.
3. `test_upload_rejects_files_larger_than_20mb` — Memverifikasi penolakan berkas >20 MB dengan pesan validasi error.
4. `test_upload_rejects_invalid_file_extensions` — Memverifikasi berkas terlarang (.exe, script) ditolak.
5. `test_dosen_can_create_material_with_external_link_alternative` — Memverifikasi pembuatan materi bertipe link eksternal.
6. `test_enrolled_student_can_download_material` — Memverifikasi mahasiswa terdaftar berhasil mengunduh berkas.
7. `test_unenrolled_student_is_forbidden_from_downloading_material` — Memverifikasi mahasiswa luar kelas ditolak dengan **HTTP 403 Forbidden**.
8. `test_guest_is_redirected_to_login_when_downloading_material` — Memverifikasi tamu tanpa autentikasi diarahkan ke halaman login.
9. `test_physical_file_is_deleted_when_material_is_deleted` — Memverifikasi penghapusan berkas fisik saat materi dihapus.
10. `test_physical_file_is_deleted_when_parent_course_is_deleted_cascade` — Memverifikasi pembersihan berkas fisik saat mata kuliah dihapus bertingkat (*cascade*).
