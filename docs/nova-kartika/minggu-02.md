

## READ
1. Baris mana di routes/web.php yang menangkapnya?
Jawab:
Request untuk halaman /tentang ditangkap oleh route ini:
```php
Route::get('/tentang', function () { return view('tentang'); });
```
Laravel akan mencari route dari atas ke bawah. Karena method yang digunakan adalah GET dan alamatnya /tentang, maka request tersebut masuk ke route ini.

2. Kalau ditangani controller, berkas dan method mana?
Jawab: Untuk sekarang route ini masih menggunakan closure, yaitu fungsi yang langsung ditulis di dalam route dan belum memakai controller. Kalau mau menggunakan controller, route ini bisa diarahkan ke file app/Http/Controllers/TentangController.php.
Method yang bisa dipakai yaitu index() atau __invoke().

3. View mana yang dikembalikan? Di path apa persisnya?
Jawaban:
View yang dipanggil adalah tentang.
File-nya ada di:
resources/views/tentang.blade.php

4. Layout apa yang membungkusnya?
Jawaban:
Untuk file tentang.blade.php bawaan Minggu 1, saat ini belum memakai layout. Isinya masih berupa HTML sendiri.
Kalau mengikuti konsep Layout dan Komponen Blade, halaman ini bisa memakai <x-layout> yang file-nya ada di resources/views/components/layout.blade.php. Di layout tersebut bisa berisi bagian yang sering dipakai seperti HTML dasar, navbar, dan @vite, jadi tidak perlu dibuat lagi di setiap halaman.

5. Jalankan php artisan route:list --path=tentang. Cocok dengan analisis Anda?
Jawaban:
Cocok. Dari perintah tersebut terlihat kalau route /tentang terdaftar dengan method GET|HEAD dan mengarah ke view tentang. Jadi route tersebut sudah terdaftar dan bisa menerima request dari browser.

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