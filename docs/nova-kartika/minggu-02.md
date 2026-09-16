

## READ
1. Baris mana di routes/web.php yang menangkapnya?
Jawab:
request untuk halaman /tentang ditangkap oleh route ini:
```php
Route::get('/tentang', function () { return view('tentang'); });
```
laravel akan mencari route dari atas ke bawah karena method yang digunakan GET dan alamatnya /tentang, maka request tersebut masuk ke route ini

1. Kalau ditangani controller, berkas dan method mana?
Jawab: untuk route ini masih pakai closure, yaitu fungsi yang langsung ditulis di dalam route dan belum pakai controller. kalau mau pakai controller, route ini bisa diarahkan ke file app/Http/Controllers/TentangController.php.
method yang bisa dipakai yaitu index() atau __invoke()

1. View mana yang dikembalikan? Di path apa persisnya?
Jawaban:
view yang dipanggil adalah tentang
file-nya ada di:
resources/views/tentang.blade.php

1. Layout apa yang membungkusnya?
Jawaban:
untuk file tentang.blade.php bawaan minggu 1, skekarang ini belum pakai layout isinya masih berupa HTML sendiri.
kalau ikutin konsep layout dan komponen blade, halaman ini bisa pakai <x-layout> yang file-nya ada di resources/views/components/layout.blade.php. di layout itu bisa berisi bagian yang sering dipakai seperti HTML dasar, navbar, dan @vite, jadi tidak perlu dibuat lagi di setiap halaman.

1. Jalankan php artisan route:list --path=tentang. Cocok dengan analisis Anda?
Jawaban:
cocok, dari perintah tsb terlihat kalau route /tentang terdaftar dengan method GET|HEAD dan mengarah ke view tentang. jadi route tersebut sudah terdaftar dan bisa menerima request dari browser.

## BREAK
| No | Yang Diubah | Prediksi Sebelum Mencoba | Pesan Error Sebenarnya | Yang Dipelajari |
|---|---|---|---|---|
| 1 | Mengganti `Route::get` menjadi `Route::post` pada route daftar mata kuliah | Mungkin halaman tidak bisa dibuka | Method HTTP tidak sesuai → 405 | Method pada route harus sesuai dengan yang digunakan |
| 2 | Mengganti nama view pada `return view(...)` dengan nama view yang tidak ada | Mungkin halaman menjadi error | View tidak ditemukan | Nama view harus sesuai dengan file yang tersedia |
| 3 | Menghapus `->name('courses.show')`, lalu membuka halaman yang memakai `route('courses.show')` | Mungkin link-nya tidak bisa digunakan | Route `courses.show` tidak ditemukan | Nama route diperlukan saat route dipanggil dengan `route()` |
| 4 | Memindahkan `/courses/{course}` ke atas `/courses/create`, lalu membuka `/courses/create` | Mungkin halaman create tetap terbuka seperti biasa | `/courses/create` dianggap sebagai nilai `{course}` | Urutan route bisa memengaruhi route yang dijalankan |
| 5 | Mengubah `{{ $nama }}` menjadi `{!! $nama !!}` dan mengisi `$nama` dengan `<script>alert('XSS')</script>` | Mungkin tulisannya hanya muncul sebagai teks | Script XSS berhasil dijalankan di halaman | Cara menampilkan data berpengaruh terhadap keamanan halaman |
| 6 | Menghapus `@vite(...)` dari layout | Mungkin tampilan halaman jadi berantakan | Aset CSS/JS tidak termuat | `@vite` digunakan untuk memuat aset yang dibutuhkan |
| 7 | Menghentikan `npm run dev`, lalu memuat ulang halaman | Mungkin tampilan halaman tidak berubah | Aset dari development server tidak tersedia/terbarui | Development server berbeda dengan hasil build |
| 8 | Memanggil `route('courses.show')` tanpa memberikan parameter | Mungkin muncul error karena ada data yang kurang | Missing required parameter | Route yang membutuhkan parameter harus diberi nilainya |

## CHECKPOINT MINGGU 2

- [✓] Kenapa menghapus data lewat GET berbahaya? Beri satu skenario konkret.
- [✓] KApa yang terjadi kalau /courses/{course} ditulis sebelum /courses/create? Kenapa?
- [-] Tunjukkan di kode Anda satu tempat yang memakai route(). Apa untungnya dibanding URL hardcode?
- [✓]Apa beda {{ }} dan {!! !!}? Peragakan XSS yang Anda buat di bagian BREAK.
- [✓] Apa fungsi @vite? Apa beda npm run dev dan npm run build?
- [✓] Jelaskan mengapa data dari Request tidak boleh dipercaya.