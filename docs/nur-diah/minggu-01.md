## READ
1. Berkas `public/index.php` menjadi pintu masuk utama ketika ada request dari browser ke aplikasi Laravel. Berkas ini menyiapkan Laravel dengan memuat Composer dan bootstrap/app.php, serta mengecek apakah aplikasi sedang dalam mode maintenance. Setelah itu, request dari browser ditangkap dan diteruskan ke Laravel untuk diproses sampai menghasilkan response.
2. Dapat terlihat dengan jelas bahwa bagian `withRouting()` digunakan untuk mengatur route dan menghubungkan aplikasi dengan file routes/web.php. Bagian `withMiddleware()` digunakan untuk mengatur middleware. Bagian `withExceptions()` digunakan untuk mengatur penanganan error atau exception.
3. .env berisi konfigurasi asli yang digunakan oleh aplikasi, seperti pengaturan database dan APP_KEY, sedangkan .env.example hanya berisi contoh konfigurasi yang digunakan sebagai acuan. File yang di-commit biasanya .env.example, sedangkan .env tidak di-commit karena bisa berisi data rahasia seperti password dan konfigurasi pribadi.
4. Pada Laravel 12, middleware didaftarkan di bootstrap/app.php pada bagian withMiddleware(). Berbeda dengan tutorial Laravel versi lama yang biasanya menggunakan app/Http/Kernel.php, Laravel 12 sudah tidak menggunakan Kernel.php untuk mengatur middleware sehingga konfigurasi tersebut dilakukan di bootstrap/app.php.

## BREAK

| #  |Yang dirusak|Prediksi Anda sebelum mencoba|Pesan error sebenarnya|
|---|---|---|---|
|1|Ganti nama .env menjadi .env.bak|Aplikasi tidak bisa membaca environment, sehingga akan terjadi error|Terjadi kegagalan konfigurasi kunci aplikasi/error konfigurasi default|
|2|Kosongkan nilai APP_KEY di .env|Meskipun file .env ada, tapi "kunci rahasia" aplikasinya kosong. Laravel butuh kunci ini untuk jalan, jadi tetap error|MissingAppKeyException: No application encryption key has been specified.|
|3|Ubah DB_DATABASE menjadi nama yang tidak ada|Kalau halaman yang dibuka butuh ambil data dari database, ini bakal gagal karena database yang dicari tidak ada. Tapi kalau halamannya cuma halaman biasa (kayak welcome page) yang tidak butuh database, mungkin tidak akan error.|SQLSTATE[HY000] [1049] Unknown database 'nama_ngasal' (MySQL) — tapi kalau halaman welcome default tidak query DB, bisa jadi tidak ada error sama sekali|
|4|Ubah APP_DEBUG=false, lalu ulangi nomor 3|Errornya tetap kejadian, detail error disembunyikan demi keamanan|Halaman generik: Server Error / 500 polos, tanpa stack trace, tanpa nama file, tanpa query SQL|

## CHECHKPOINT
1. Urutan berkas yang dilewati request (browser → HTML)
- `public/index.php` — satu-satunya pintu masuk semua request.
- `vendor/autoload.php` — dimuat oleh index.php, mengaktifkan semua library lewat Composer.
- `bootstrap/app.php` — membangun instance aplikasi, mendaftarkan routing, middleware, exception handling.
- `routes/web.php` — Laravel mencocokkan URL request dengan route yang ada di sini.
- Middleware (terdaftar di `bootstrap/app.php`) — dijalankan sebelum request sampai ke tujuan.
- Controller (kalau route memanggilnya) di `app/Http/Controllers/`, atau langsung closure di `web.php`.
- View di `resources/views/*.blade.php` — dirender jadi HTML.
- HTML dikembalikan sebagai Response, keluar lewat `index.php`, balik ke browser.

2. Karena `public/` didesain sebagai satu-satunya folder yang "aman" untuk publik — isinya cuma asset (CSS, JS, gambar) dan `index.php` sebagai gerbang masuk. Semua logic sensitif (kode aplikasi, konfigurasi database, file `.env`) sengaja ditaruh **di luar** `public/`. Kalau seluruh folder proyek diekspos, siapa pun bisa langsung membuka file seperti `.env` (isinya password database, API key, app key) lewat URL, atau bahkan kode PHP sumber (`app/`, `routes/`) yang membocorkan cara sistem bekerja — celah besar untuk serangan.

3. Beda `.env` dan `.env.example`
- `.env` — berisi konfigurasi **asli** dan sensitif untuk environment kamu (password DB sungguhan, APP_KEY sungguhan, dll). Beda-beda di tiap komputer/server.
- `.env.example` — cuma **template/contoh**, isinya nama variabel tanpa nilai rahasia (atau nilai dummy), supaya orang lain tahu variabel apa saja yang dibutuhkan.
Yang di-commit ke Git hanya `.env.example`, karena `.env` asli mengandung kredensial rahasia — kalau ikut ter-commit dan repo-nya publik (atau bahkan private tapi bocor), semua orang bisa lihat password database dan kunci enkripsi kamu.

4. Di `bootstrap/app.php`, lewat method `->withMiddleware()`. Jawabannya beda dari kebanyakan tutorial internet karena banyak tutorial masih mengacu ke Laravel versi lama (sebelum Laravel 11), di mana middleware didaftarkan di `app/Http/Kernel.php`. Kernel.php sudah dihapus mulai Laravel 11 — strukturnya disederhanakan, semua konfigurasi bootstrap (middleware, routing, exception handling) dipusatkan ke satu file: `bootstrap/app.php`.

5. Kalau terjadi error, halaman akan menampilkan detail teknis lengkap ke siapa pun yang mengaksesnya: isi query SQL (bisa membocorkan struktur tabel/nama kolom), path lengkap file di server, stack trace kode, bahkan kadang nilai variabel environment. Penyerang bisa memakai info ini untuk memetakan celah keamanan — misalnya tahu versi framework, struktur database, atau lokasi file sensitif — tanpa perlu meretas apa pun, cukup memicu satu error.

6. `git log` dengan commit atas nama sendiri
Ini bagian yang harus dijalankan sendiri di terminal, karena hasilnya bergantung pada konfigurasi Git di komputermu.