*READ*

1. file ``public/index.php`` merupakan file pertama yang dijalankan ketika lravel dijalankan. file ini yang mengecek apakah aplikasi dengan maintenence, memuat file yang akan dijalankan, dan menjalankan apikasi laravel
2. bagian ``withRouting()`` digunakaan untuk mengatur route, bagian ``withMiddleware()`` digunkana untuk mengatur middleware, dan untuk exception menggunakan ``withexception``
3. route pada ``routes/web.php`` menjadi halaman selamat datang dengan memanggil view welcome, cara mengubah teks yang tampil adalah dengan mengedit ``resources/views/welcome.blade.php``.
4. Hasil nya cocok dengan isi ``routes/web.php``. routes berasal dari kode yang ada di ``routes/web.php`` dan mengembalikan view welcome.

*BREAK*

|No | Yang dirusak | Prediksi Sebelum mencoba | Pesan error Sebenarnya|
|---|--------------|--------------------------|-----------------------|
|1 |Ganti nama ``.env`` menjadi ``.env.bak`` |Laravel akan eror|Laravel tidak akan membaca file konfigurasi tersebut sebagai env
|2 |Kosongkan nilai ``APP_KEY`` di ``.env`` |Code tidak jalan|Laravel tidak memiliki kunci enkripsi aplikasi
|3 |Ubah ``DB_DATABASE`` menjadi nama yang tidak ada |Database tidak jalan|MySQL tidak menemukan database dengan nama tersebut 
|4 |Ubah ``APP_DEBUG=false``, lalu ulangi nomor 3|Laravel mengalami eror|APP_DEBUG=false membuat Laravel menyembunyikan detail kesalahan dari pengguna