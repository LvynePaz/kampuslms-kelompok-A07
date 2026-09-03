## READ
1. Berkas `public/index.php` menjadi pintu masuk utama ketika ada request dari browser ke aplikasi Laravel. Berkas ini menyiapkan Laravel dengan memuat Composer dan bootstrap/app.php, serta mengecek apakah aplikasi sedang dalam mode maintenance. Setelah itu, request dari browser ditangkap dan diteruskan ke Laravel untuk diproses sampai menghasilkan response.
2. Dapat terlihat dengan jelas bahwa bagian `withRouting()` digunakan untuk mengatur route dan menghubungkan aplikasi dengan file routes/web.php. Bagian `withMiddleware()` digunakan untuk mengatur middleware. Bagian `withExceptions()` digunakan untuk mengatur penanganan error atau exception.
3. Pada `routes/web.php`` terdapat `route /` yang digunakan untuk menampilkan halaman welcome dengan `return view('welcome')`. Kemudian ubah teks pada halaman `welcome.blade.php` di folder `resources/views`. Setelah browser dimuat ulang, teks yang sudah diubah tampil sehingga dapat dipastikan bahwa perubahan berhasil.
4. Setelah menjalankan perintah `php artisan route:list`, muncul daftar route yang tersedia pada aplikasi Laravel. Hasil tersebut dapat dicocokkan dengan isi `routes/web`.php, salah satunya `route GET|HEAD /` yang berasal dari `Route::get('/')`. Hal ini menunjukkan bahwa route / yang dibuat di web.php sudah dikenali oleh Laravel.

## BREAK

| #  |Yang dirusak|Prediksi Anda sebelum mencoba|Pesan error sebenarnya|
|---|---|---|---|
|1|Ganti nama .env menjadi .env.bak|Aplikasi tidak bisa membaca environment, sehingga akan terjadi error|Terjadi kegagalan konfigurasi kunci aplikasi/error konfigurasi default|
|2|Kosongkan nilai APP_KEY di .env|Meskipun file .env ada, tapi "kunci rahasia" aplikasinya kosong. Laravel butuh kunci ini untuk jalan, jadi tetap error|MissingAppKeyException: No application encryption key has been specified.|
|3|Ubah DB_DATABASE menjadi nama yang tidak ada|Kalau halaman yang dibuka butuh ambil data dari database, ini bakal gagal karena database yang dicari tidak ada. Tapi kalau halamannya cuma halaman biasa (kayak welcome page) yang tidak butuh database, mungkin tidak akan error.|SQLSTATE[HY000] [1049] Unknown database 'nama_ngasal' (MySQL) — tapi kalau halaman welcome default tidak query DB, bisa jadi tidak ada error sama sekali|
|4|Ubah APP_DEBUG=false, lalu ulangi nomor 3|Errornya tetap kejadian, detail error disembunyikan demi keamanan|Halaman generik: Server Error / 500 polos, tanpa stack trace, tanpa nama file, tanpa query SQL|