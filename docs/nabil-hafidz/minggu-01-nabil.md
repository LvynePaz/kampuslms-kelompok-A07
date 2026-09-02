**Check Point Minggu 1**
1. Berkas public/index.php adalah titik awal (entry point) aplikasi Laravel yang menerima setiap request dari pengguna.
Pertama, berkas ini mengecek mode maintenance, lalu memuat Composer autoloader agar library Laravel dapat digunakan.
Setelah itu, berkas ini menjalankan Laravel melalui bootstrap/app.php dan meneruskan request untuk diproses oleh aplikasi.
2. Route: bagian ->withRouting(...) — mengatur lokasi file route seperti routes/web.php.
Middleware: bagian ->withMiddleware(function (Middleware $middleware) { ... }) — tempat untuk mengatur middleware.
Exception: bagian ->withExceptions(function (Exceptions $exceptions) { ... }) — tempat untuk mengatur penanganan error/exception.
3. Route yang menghasilkan halaman selamat datang terdapat pada routes/web.php, yaitu Route::get('/', function () { return view('welcome'); });. Route tersebut memanggil file welcome.blade.php yang berada di folder resources/views. Untuk mengubah teks halaman, ubah teks di file welcome.blade.php, lalu simpan dan muat ulang browser untuk memastikan perubahan tampil.
4. Di routes/web.php ada:

Route::get('/', function () {
    return view('welcome');
});

Dan pada php artisan route:list muncul:

GET|HEAD  /  routes/web.php:5

Artinya route / dari web.php sudah terdaftar dengan benar. GET|HEAD menunjukkan metode HTTP yang bisa digunakan untuk mengakses halaman tersebut.