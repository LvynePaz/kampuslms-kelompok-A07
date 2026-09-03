# Catatan Individu Minggu 01 - Patra Ananda (10241061)

## 1. Progres Pengerjaan
- Inisialisasi dan setup awal framework Laravel 12.
- Pengaturan Git repository dan branching kelompok.
- Mempelajari alur HTTP Request dan struktur direktori Laravel 12.
- Pembuatan dan pengujian route `/tentang` beserta tampilan profil kelompok.

---

## 2. Hasil Eksplorasi (Read - Break - Fix)

### READ

1.  **public/index.php**: Berfungsi sebagai pintu masuk request browser ke aplikasi Laravel. Memuat autoloader dan mengeksekusi kejadian penanganan request.
2. **bootstrap/app.php**: Pusat konfigurasi di Laravel 12 untuk routing, middleware, dan exception.
3. **routes/web.php**: Definisi rute untuk aplikasi web Laravel, menentukan bagaimana URL dipetakan ke controller.
4. berdasarkan hasil dari ```php artisan route:list``` suda cocok dengan isi routes/web.php. karna  hasilnya adalah 
``` 
| Method   | URI              |
| -------- | ---------------- |
| GET/HEAD | `/`              |
| GET/HEAD | `storage/{path}` |
| PUT      | `storage/{path}` |
| GET/HEAD | `tentang`        |
| GET/HEAD | `up`             |

```
Diliat dari tabel ini untuk GET URL "/" itu ada pada routes/web.php:

```php
Route::get('/', function () {
    return view('welcome');
});
```
Untuk PUT `storage/{path}` dan GET `/up` itu bawaan dari laravel.
---

### BREAK

| # | Yang dirusak | Prediksi Anda sebelum mencoba | Pesan error sebenarnya |
|---|--------------|-------------------------------|------------------------|
| 1 | Ganti nama `.env` menjadi `.env.bak` | | |
| 2 | Kosongkan nilai `APP_KEY` di `.env` | | |
| 3 | Ubah `DB_DATABASE` menjadi nama yang tidak ada | | |
| 4 | Ubah `APP_DEBUG=false`, lalu ulangi nomor 3 | | |



## 4. CHECKPOINT — Pertanyaan Mandiri
- [x] Alur request dari browser sampai HTML kembali.
- [x] Alasan hanya folder `public/` yang diekspos ke internet.
- [x] Perbedaan `.env` dan `.env.example`.
- [x] Pendaftaran middleware di Laravel 12 pada `bootstrap/app.php`.
- [x] Risiko `APP_DEBUG=true` di lingkungan production.
