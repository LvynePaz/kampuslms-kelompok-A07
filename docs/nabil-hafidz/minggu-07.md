*READ*

1. Permintaan POST login ditangani oleh controller `app/Http/Cintrollers/Auth.AuthenticatedsessionCOntroller.php` pada method `store()`

2. `Auth::attempt()` dipanggil di berkas `app/Http/Requests/Auth/LoginRequest.php` pada baris 45 dengan method `Authenticate()` 

3. Perintah `$request->session()->regenerate()` ada di `AuthenticatedSessionController.php` pada baris 29. kode ini ditaruh tepat setelah user login untuk mencegah serangan session fixation, sebelum login browser sudah memiliki session ID sebagai tamu, dengan me-regenerate ID session, sistem akan membuang ID sesi lama dan memberikan ID baru untuk mencegah akun dibajak oleh pihak yang mengetahui ID session sebelum login

4. Kata sandi di-hash secara otomatis melalui method casts() di berkas app/Models/User.php pada baris 51 dengan aturan 'password' => 'hashed'. Berkat fitur cast ini, setiap kali akun baru dibuat atau password diubah, Laravel otomatis mengubah password teks biasa menjadi hash bcrypt sebelum disimpan ke database

5. Sebelum login, cookie laravel_session memiliki suatu nilai hash acak sesi tamu.
Setelah berhasil login, nilai cookie laravel_session tersebut langsung berubah menjadi hash yang sama sekali baru

6. Ketika saya logout lalu menekan tombol Back di browser, halaman dashboard sebelumnya sempat muncul sekilas di layar. Hal ini terjadi karena browser memuat salinan halaman dari memori cache lokalnya (BFCache).
Namun, saat saya refresh halaman atau mengklik salah satu menu, sistem langsung menolak dan melempar saya kembali ke halaman /login
---

*BREAK*

| # | Yang dicoba | Perilaku yang Terjadi | Penjelasan & Bahaya Keamanannya |
|---|-------------|-----------------------|---------------------------------|
| 1 | Hapus `session()->regenerate()` dari login| Login tetap sukses, tetapi nilai cookie session di browser tidak berganti sama sekali. | **Celah Session Fixation.** Penyerang bisa menyusupkan ID session yang sudah diketahui sebelumnya dan ikut masuk ke akun pengguna setelah login. |
| 2 | Hapus `Gate::authorize()` dari `update`,tapi biarkan `@can` di blade | Tombol Edit di tampilan web memang tidak muncul, tetapi data mata kuliah tetap bisa diubah saat ditembak langsung dengan `PUT` via cURL. | **Otorisasi hanya di sisi UI, bukan server.** Pengamanan aplikasi web wajib dilakukan di controller menggunakan `Gate::authorize()`, bukan hanya menyembunyikan tombol di Blade. |
| 3 |Sebagai dosen A, kirim PUT ke mata kuliah dosen B lewat `curl`| Server menolak dan mengembalikan respon **403 Forbidden**. | **Proteksi Policy berhasil.** `CoursePolicy` memeriksa kepemilikan data (`lecturer_id === user.id`), sehingga dosen tidak bisa mengotak-atik kelas dosen lain. |
| 4 | Sebagai mahasiswa, buka submission mahasiswa lain (IDOR minggu 5)| Server merespons dengan **403 Forbidden**. | **Pencegahan celah IDOR.** `SubmissionPolicy` memastikan berkas tugas hanya bisa dilihat oleh pemilik tugas, dosen pengampu, atau admin. |
| 5 | Ubah `index` menjadi `Course::paginate()` polos, login sebagai mahasiswa | Mahasiswa dapat melihat semua mata kuliah di kampus, termasuk mata kuliah dari jurusan lain yang tidak diikutinya. | **Kebocoran data di level daftar (List Leak).** Penyaringan data wajib dilakukan sejak awal pada query database berdasarkan role user yang aktif. |
| 6 |Kirim `role=admin` pada form edit profil | Nilai role di database tidak berubah (tetap role aslinya). | **Proteksi Mass Assignment.** Kolom `role` tidak dibuka di `$fillable` dan divalidasi ketat sehingga pengguna biasa tidak bisa menaikkan hak aksesnya sendiri (*privilege escalation*). |
| 7 | Ganti cast `hashed` menjadi tidak ada, buat user baru, lihat kolom password di database. | Password tersimpan dalam bentuk teks biasa (plain text) di database. | **Sangat berbahaya jika database bocor.** Password wajib selalu di-hash agar tidak dapat dibaca langsung oleh penyerang jika data bocor. |
| 8 |Login, salin cookie session, tempel di browser lain | Browser B langsung masuk ke akun yang sama tanpa diminta login. | **Bahaya pembajakan sesi (Session Hijacking).** Menunjukkan sesi sepenuhnya dikendalikan cookie, sehingga cookie harus diproteksi dengan atribut `HttpOnly` dan HTTPS. |