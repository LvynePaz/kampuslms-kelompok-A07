## READ

1. POST `/login` diproses oleh method `store()` pada
[`AuthenticatedSessionController`](../../app/Http/Controllers/Auth/AuthenticatedSessionController.php). Method tersebut memanggil `$request->authenticate()`, kemudian menjalankan:

    ```php
    $request->session()->regenerate();
    ```

2. Pemanggilan autentikasi yang setara dengan `Auth::attempt()` berada di method
    `authenticate()` pada
    [`LoginRequest`](../../app/Http/Requests/Auth/LoginRequest.php). Kode tersebut
    menggunakan:

    ```php
    Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))
    ```

    Jika kredensial salah, pesan yang digunakan adalah `auth.failed` yang sama untuk
    email tidak terdaftar maupun password yang salah. Ini mencegah *user
    enumeration*. Percobaan login dibatasi lima kali melalui `RateLimiter`.
    
3. Regenerasi session (`session()->regenerate()) dilakukan setelah autentikasi berhasil, bukan sebelum autentikasi. Tujuannya adalah mengganti ID session agar ID yang mungkin sudah ditanamkan penyerang tidak tetap digunakan setelah korban login.

    Pada method `destroy()` di
    [`AuthenticatedSessionController`](../../app/Http/Controllers/Auth/AuthenticatedSessionController.php),
    logout sudah melakukan tiga langkah yang benar:

    ```php
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    ```

    Dengan demikian, session lama tidak dapat dipakai sebagai session pengguna yang masih aktif. Secara browser, setelah logout tombol *back* mungkin masih menampilkan halaman dari cache, tetapi request baru ke endpoint yang dilindungi seharusnya ditolak karena autentikasi sudah dihapus.

4. Kata sandi di-hash terdapat dalam Model [`User`](../../app/Models/User.php) mempunyai cast:

    ```php
    'password' => 'hashed',
    ```

    Artinya password otomatis di-hash saat disimpan melalui Eloquent. Password bukan data yang dapat dikembalikan ke bentuk asli. Fitur lupa password karena itu menggunakan token reset, bukan menampilkan password lama.

    Pada controller admin, password baru juga diproses menggunakan `Hash::make()`.
    Hasil akhirnya tetap berupa hash dan tidak berupa teks biasa.

5. Sebelum Login: Cookie session (misal: laravel_session) berisi string hash unik yang mewakili sesi guest (anonim).

    Sesudah Login: Nilai Cookie laravel_session berubah sepenuhnya menjadi string hash ID baru akibat pemanggilan session()->regenerate().

    Sebelum login, browser mungkin sudah memiliki cookie session untuk pengguna yang belum terautentikasi. Setelah login berhasil, session ID seharusnya diregenerasi sehingga nilai cookie session berubah. Nilai sebenarnya harus diperiksa langsung melalui DevTools karena dapat berbeda sesuai kondisi proyek dan browser.

6. Halaman dashboard atau halaman privat sebelumnya masih sempat terlihat di layar browser. Namun, ketika halaman di-refresh atau ada tombol/aksi yang diklik, sistem langsung menolak dan mengarahkan kembali (redirect) ke halaman `/login`.
   Penyebabnya:
   - Di sisi browser: Halaman tersebut muncul bukan karena sesi login masih aktif, melainkan karena browser menampilkan tampilan yang tersimpan di dalam tembolok (cache) lokal browser.
   - Di sisi server: Sesi pengguna sebenarnya sudah dihapus/dihancurkan saat proses logout (Auth::logout() dan $request->session()->invalidate()). Oleh karena itu, setiap kali ada request baru ke server, middleware autentikasi akan menolaknya karena pengguna sudah dianggap sebagai guest.

## BREAK

| # | Yang dicoba | Yang harus Anda amati | Prediksi | Yang saya pelajari |
|---|---|---|---|---|
| 1 | Hapus `session()->regenerate()` dari login | Session fixation terbuka | Penyerang yang tahu ID session pengguna sebelum login dapat terus memakai ID session tersebut untuk masuk setelah pengguna berhasil login | session()->regenerate() penting untuk mengganti ID session setelah login berhasil agar session sebelum login tidak terus digunakan |
| 2 | Hapus `Gate::authorize()` dari `update`, tapi biarkan `@can` di Blade | Tombol hilang, URL tetap jalan | Request langsung ke URL dapat berhasil meskipun tombol edit disembunyikan, apabila endpoint tidak memiliki perlindungan lain | @can hanya mengatur tampilan tombol, bukan mengamankan endpoint. Gate::authorize() tetap diperlukan untuk memeriksa hak akses di sisi server |
| 3 | Sebagai dosen A, kirim PUT ke mata kuliah dosen B lewat `curl` | Uji Policy Anda sungguhan | Request seharusnya ditolak dengan status 403 Forbidden jika Policy sudah diterapkan dengan benar | Policy membatasi tindakan berdasarkan hak akses dan kepemilikan objek. Pengujian melalui curl membantu membuktikan bahwa perlindungan server tetap berjalan tanpa melalui tampilan web |
| 4 | Sebagai mahasiswa, buka submission mahasiswa lain (IDOR minggu 5) | Sekarang harus 403 | Akses ke berkas atau detail submission mahasiswa lain ditolak oleh server dengan status HTTP 403 Forbidden | IDOR terjadi ketika pengguna dapat mengakses data milik orang lain dengan mengganti ID pada URL atau request. Policy harus memastikan pengguna hanya dapat mengakses data yang menjadi haknya |
| 5 | Ubah `index` menjadi `Course::paginate()` polos, login sebagai mahasiswa | Kebocoran tingkat daftar | Mahasiswa berpotensi melihat seluruh mata kuliah karena query tidak membatasi data berdasarkan keanggotaan pengguna | Pembatasan akses harus diterapkan pada query database, bukan hanya pada tampilan. Pengecekan otorisasi tidak hanya dilakukan per objek individual, melainkan query pada method index() wajib dibatasi (query-level filtering) sesuai peran pengguna |
| 6 | Kirim `role=admin` pada form edit profil | Mass assignment kembali diuji | Jika field role dapat diproses tanpa perlindungan, pengguna berpotensi meningkatkan hak aksesnya menjadi admin. Jika field dilindungi dengan benar, perubahan role harus ditolak atau diabaikan | Mass assignment dapat memungkinkan pengguna mengubah kolom sensitif melalui request. Field role harus dikecualikan dari data yang dapat diperbarui pengguna biasa dan hanya boleh diubah melalui proses yang berwenang |
| 7 | Ganti cast `hashed` menjadi tidak ada, buat user baru, lihat kolom password di database | Kata sandi polos di database | Kolom password di database akan menyimpan teks mentah (plain text) tanpa proses hashing | Kata sandi harus di-hash sebelum disimpan. Cast hashed membantu Laravel melakukan hashing otomatis sehingga kata sandi asli tidak tersimpan langsung di database |
| 8 | Login, salin cookie session, tempel di browser lain | Kenapa cookie harus HttpOnly & Secure | Sesi login bisa digunakan di browser lain selama cookie session valid dan tidak terenkripsi/terikat pada flag keamanan yang ketat |Cookie session wajib disetting HttpOnly (agar tidak bisa dicuri via XSS) dan Secure (agar hanya dikirim via HTTPS) guna meminimalkan risiko pencurian sesi |

## Checkpoint Minggu 7

- [ ] Apa beda autentikasi dan otorisasi? Tunjukkan satu contoh masing-masing di kode Anda.
- [ ] Tunjukkan Policy yang **Anda** tulis. Jelaskan tiap barisnya.
- [ ] Kenapa `@can` di Blade tidak cukup? Peragakan dengan mengakses URL langsung.
- [ ] Buka `docs/keamanan.md`. Pilih satu baris, jelaskan bagaimana ia ditutup, lalu buktikan dengan `curl`.
- [ ] Kenapa kata sandi di-hash, bukan dienkripsi? Apa konsekuensinya untuk fitur lupa password?
- [ ] Apa fungsi `session()->regenerate()` saat login?
- [ ] Kenapa daftar mata kuliah tidak boleh diambil semua lalu disaring di view?
- [ ] Tunjukkan satu bagian yang Anda tulis dengan bantuan AI. Apa yang Anda ubah, dan kenapa?
