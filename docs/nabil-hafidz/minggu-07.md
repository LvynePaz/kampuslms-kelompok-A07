# Catatan Individu Minggu - 07 - Nabil Hafidz (10241055)

*READ*

1. Berkas yang menangani proses POST login adalah `app/Http/Controllers/Auth/AuthenticatedSessionController.php` pada method `store()`.
   Rutenya didefinisikan di `routes/auth.php`:
   ```php
   Route::post('login', [AuthenticatedSessionController::class, 'store']);
   ```
   Ketika pengguna mengirimkan form login, request diarahkan ke method `store()`. Di sini kredensial divalidasi dan diotentikasi lewat form request `LoginRequest`, kemudian session di-regenerate sebelum diarahkan ke halaman dashboard.

2. Pemanggilan `Auth::attempt()` berada di berkas `app/Http/Requests/Auth/LoginRequest.php` di dalam method `authenticate()`:
   ```php
   if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
       RateLimiter::hit($this->throttleKey());

       throw ValidationException::withMessages([
           'email' => trans('auth.failed'),
       ]);
   }
   ```
   Method `Auth::attempt()` mencocokkan input email dan password dengan data di tabel `users`. Jika gagal, rate limiter dinaikkan untuk membatasi percobaan login berikutnya guna mencegah serangan brute-force.

3. Kode `session()->regenerate()` ditemukan di berkas `app/Http/Controllers/Auth/AuthenticatedSessionController.php` pada method `store()`:
   ```php
   $request->authenticate();
   $request->session()->regenerate();
   ```
   Perintah ini digunakan untuk mencegah serangan *session fixation*. Sebelum login, pengguna sudah memiliki ID sesi bawaan sebagai tamu. Begitu pengguna berhasil login, ID sesi diganti dengan yang baru agar ID sesi lama tidak bisa disalahgunakan oleh pihak lain untuk membajak akun.

4. Kata sandi di-hash secara otomatis menggunakan cast pada model `app/Models/User.php`:
   ```php
   protected function casts(): array
   {
       return [
           'email_verified_at' => 'datetime',
           'password' => 'hashed',
       ];
   }
   ```
   Dengan cast `'password' => 'hashed'`, setiap kali nilai password diisi atau diperbarui, Laravel langsung mengubahnya menjadi format hash bcrypt satu arah sebelum disimpan ke database, sehingga password tidak pernah tersimpan dalam bentuk teks biasa (plain text).

5. Saya memeriksa DevTools (F12) pada menu Application → Cookies untuk domain aplikasi sebelum dan sesudah login:
   * **Sebelum login:** Terdapat cookie `laravel_session` dengan nilai ID sesi acak tamu.
   * **Sesudah login:** Nilai cookie `laravel_session` otomatis berganti menjadi string acak baru.
   Hal ini membuktikan bahwa `session()->regenerate()` berhasil memperbarui ID sesi setelah autentikasi sukses.

6. Ketika saya menekan tombol logout lalu menekan tombol back di browser:
   * Tampilan dashboard sebelumnya sempat terlihat di layar karena browser memuatnya dari cache lokal memori (BFCache).
   * Namun saat halaman di-refresh atau menu lain diklik, server langsung menolak dan mengarahkan kembali ke halaman login karena sesi di server sudah dihapus lewat `Auth::logout()` dan `session()->invalidate()`.

---

*BREAK*

| # | Yang dicoba | Perilaku yang Terjadi | Penjelasan & Bahaya Keamanannya |
|---|-------------|-----------------------|---------------------------------|
| 1 | Hapus `session()->regenerate()` dari proses login di `AuthenticatedSessionController`. | Login tetap sukses, tetapi nilai cookie session di browser tidak berganti sama sekali. | **Celah Session Fixation.** Penyerang bisa menyusupkan ID session yang sudah diketahui sebelumnya dan ikut masuk ke akun pengguna setelah login. |
| 2 | Hapus `Gate::authorize()` di method update controller, tetapi tombol Edit di Blade tetap dibungkus `@can`. | Tombol Edit di tampilan web memang tidak muncul, tetapi data mata kuliah tetap bisa diubah saat ditembak langsung dengan `PUT` via cURL. | **Otorisasi hanya di sisi UI, bukan server.** Pengamanan aplikasi web wajib dilakukan di controller menggunakan `Gate::authorize()`, bukan hanya menyembunyikan tombol di Blade. |
| 3 | Kirim request `PUT` untuk mengubah mata kuliah milik Dosen B menggunakan akun Dosen A via cURL. | Server menolak dan mengembalikan respon **403 Forbidden**. | **Proteksi Policy berhasil.** `CoursePolicy` memeriksa kepemilikan data (`lecturer_id === user.id`), sehingga dosen tidak bisa mengotak-atik kelas dosen lain. |
| 4 | Login sebagai Mahasiswa A, lalu coba akses URL submission milik Mahasiswa B (`GET /submissions/{id}`). | Server merespons dengan **403 Forbidden**. | **Pencegahan celah IDOR.** `SubmissionPolicy` memastikan berkas tugas hanya bisa dilihat oleh pemilik tugas, dosen pengampu, atau admin. |
| 5 | Ubah query `CourseController@index` menjadi `Course::paginate(15)` tanpa filter role, lalu buka pakai akun mahasiswa. | Mahasiswa dapat melihat semua mata kuliah di kampus, termasuk mata kuliah dari jurusan lain yang tidak diikutinya. | **Kebocoran data di level daftar (List Leak).** Penyaringan data wajib dilakukan sejak awal pada query database berdasarkan role user yang aktif. |
| 6 | Kirim parameter tambahan `role=admin` saat submit form edit profil atau update akun. | Nilai role di database tidak berubah (tetap role aslinya). | **Proteksi Mass Assignment.** Kolom `role` tidak dibuka di `$fillable` dan divalidasi ketat sehingga pengguna biasa tidak bisa menaikkan hak aksesnya sendiri (*privilege escalation*). |
| 7 | Hapus cast `'password' => 'hashed'` di model `User`, lalu simpan user baru. | Password tersimpan dalam bentuk teks biasa (plain text) di database. | **Sangat berbahaya jika database bocor.** Password wajib selalu di-hash agar tidak dapat dibaca langsung oleh penyerang jika data bocor. |
| 8 | Salin nilai cookie `laravel_session` dari browser A yang sudah login, lalu tempel ke browser B (Incognito) dan refresh. | Browser B langsung masuk ke akun yang sama tanpa diminta login. | **Bahaya pembajakan sesi (Session Hijacking).** Menunjukkan sesi sepenuhnya dikendalikan cookie, sehingga cookie harus diproteksi dengan atribut `HttpOnly` dan HTTPS. |

---

*BUILD*

Pada Milestone M2 ini, beberapa tugas implementasi keamanan dan otorisasi yang telah dikerjakan di proyek kelompok (`kampuslms-kelompok-A07`):

1. **Autentikasi Starter Kit Breeze:**
   Mengintegrasikan Breeze Blade untuk alur login, logout aman via method `POST` dengan proteksi token `@csrf`, regenerasi sesi, dan manajemen profil pengguna.

2. **5 Policy Otorisasi:**
   Menerapkan otorisasi berbasis peran dan kepemilikan untuk seluruh model:
   * `CoursePolicy` (pengelolaan mata kuliah)
   * `MaterialPolicy` (akses dan pengunduhan materi)
   * `AssignmentPolicy` (pembuatan dan pengaturan tugas)
   * `SubmissionPolicy` (pengumpulan dan peninjauan tugas)
   * `GradePolicy` (penilaian tugas mahasiswa)

3. **Integrasi Controller:**
   Mengganti pengecekan manual lama dengan `Gate::authorize()` pada controller (`CourseController`, `MaterialController`, `AssignmentController`).

4. **Filter Query Berdasarkan Peran:**
   Menyaring query pada method `index()`:
   * Admin: melihat seluruh daftar mata kuliah.
   * Dosen: hanya melihat mata kuliah yang diampunya.
   * Mahasiswa: hanya melihat mata kuliah yang telah diikutinya.

5. **Pembaruan Antarmuka (Navbar & Blade):**
   * Menggunakan direktif `@guest` untuk menampilkan tautan login bagi pengunjung yang belum masuk.
   * Menggunakan direktif `@auth` untuk menampilkan identitas pengguna, lencana peran, menu mata kuliah dinamis, dan tombol logout yang aman.
