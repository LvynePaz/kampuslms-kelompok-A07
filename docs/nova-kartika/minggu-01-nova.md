1. public/index.php adalah file awal yang menjalankan aplikasi Laravel saat ada request dari pengguna. File ini mengecek apakah Laravel sedang dalam mode maintenance lalu memuat file yang dibutuhkan untuk menjalankan aplikasi. Lalu request dari pengguna diterima dan diproses Laravel.
Route → ->withRouting(...) untuk mengatur alamat halaman yang ada di aplikasi.
Middleware → ->withMiddleware(...) untuk mengatur proses sebelum request dijalankan.
Exception → ->withExceptions(...) untuk mengatur error yang terjadi di aplikasi.