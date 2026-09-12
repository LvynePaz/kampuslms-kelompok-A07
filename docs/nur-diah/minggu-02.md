## READ
1. Route yang menangkap `/tentang` berada di `routes/web.php`, yaitu 
```php
Route::get('/tentang', function () { return view('tentang'); });
```
Route tersebut menggunakan method `GET` sehingga akan dijalankan ketika browser mengakses URL `/tentang`.

2. Route `/tentang` tidak ditangani oleh Controller. Route tersebut langsung menggunakan anonymous function atau closure yang ditulis setelah `Route::get()`.

3. View yang dikembalikan adalah `view('tentang')`. View tersebut berada di `resources/views/tentang.blade.php`.

4. Untuk mengetahui layout yang digunakan, saya perlu melihat isi tentang.blade.php. Jika di dalamnya terdapat `@extends('layouts.app')`, berarti layout yang digunakan adalah `resources/views/layouts/app.blade`.php. Jika tidak terdapat `@extends`, berarti halaman tersebut tidak menggunakan layout tersebut.

5. Setelah menjalankan `php artisan route:list --path=tentang`, route `/tentang` akan muncul sebagai route dengan method `GET|HEAD` dan menggunakan Closure sebagai action. Hasil tersebut sesuai dengan kode di `routes/web.php` karena route `/tentang` memang menggunakan `Route::get()` dan anonymous function, bukan Controller.

## BREAK

| #  |Yang dirusak|Prediksi Anda sebelum mencoba|Pesan error sebenarnya|
|---|---|---|---|
|1|Ubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah|Halaman tidak dapat dibuka karena route hanya menerima `POST`, sedangkan browser mengakses halaman menggunakan `GET`|405 Method Not Allowed. Request GET tidak sesuai dengan route yang hanya menerima POST|
|2|Ubah nama view di return `view(...)` menjadi yang tidak ada|Laravel akan error karena file view yang dipanggil tidak ditemukan|`View [nama-view] not found`. Laravel tidak menemukan file Blade sesuai nama view yang dipanggil|
|3|Hapus `->name('courses.show')`, lalu muat halaman yang memakai `route('courses.show')`|Laravel tidak dapat membuat URL karena route dengan nama tersebut sudah tidak terdaftar|`Route [courses.show] not defined`. Route dengan nama courses.show tidak ditemukan|
|4|Pindahkan `/courses/{course}` ke ATAS `/courses/create`, lalu buka `/courses/create`|Laravel akan menganggap `create` sebagai nilai dari `{course}` sehingga route detail course yang dijalankan|Hasilnya `/courses/create` dapat masuk ke route `/courses/{course}`, sehingga create dianggap sebagai parameter `course`, bukan membuka halaman create|
|5|Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert('XSS')</script>`|Script akan dijalankan karena `{!! !!}` tidak melakukan escaping terhadap HTML|Muncul pop-up XSS di browser. Ini menunjukkan script JavaScript berhasil dijalankan dan merupakan contoh XSS|
|6|Hapus `@vite(...)` dari layout|Tampilan halaman akan berubah karena CSS/JavaScript yang sebelumnya dimuat melalui Vite tidak lagi dipanggil|CSS/JS tidak termuat sebagaimana mestinya, sehingga tampilan dapat menjadi berantakan atau fungsi JavaScript tertentu tidak berjalan|
|7|Hentikan `npm run dev` lalu muat ulang halaman|Asset yang membutuhkan Vite development server tidak akan dapat dimuat|Asset dari Vite tidak dapat dimuat ataupun muncul masalah koneksi ke Vite, sehingga CSS atau JavaScript tidak bekerja seperti saat `npm run dev` berjalan|
|8|Panggil `route('courses.show')` tanpa mengirim parameter|Laravel akan error karena route `courses.show` membutuhkan parameter `{course}`, tetapi parameternya tidak diberikan|`Missing required parameter for [Route: courses.show]` karena parameter course wajib diberikan|

## CHECKPOINT MINGGU 2

- [✓] Kenapa menghapus data lewat GET berbahaya? Beri satu skenario konkret.
- [✓] KApa yang terjadi kalau /courses/{course} ditulis sebelum /courses/create? Kenapa?
- [] Tunjukkan di kode Anda satu tempat yang memakai route(). Apa untungnya dibanding URL hardcode?
- [✓]Apa beda {{ }} dan {!! !!}? Peragakan XSS yang Anda buat di bagian BREAK.
- [✓] Apa fungsi @vite? Apa beda npm run dev dan npm run build?
- [✓] Jelaskan mengapa data dari Request tidak boleh dipercaya.
