# Catatan Individu Minggu 02 - Patra Ananda (10241061)

## 1. Progres Pengerjaan
- Mempelajari konsep arsitektur MVC (Model-View-Controller) pada Laravel 12.
- Memahami alur request HTTP, penamaan route (*named routes*), dan proteksi method HTTP.
- Mempelajari pembuatan layout bersama menggunakan komponen Blade (`<x-layout>`).
- Melakukan analisis dan pencegahan kerentanan keamanan XSS (Cross-Site Scripting).
- Menyelesaikan tugas **FIX Minggu 02 (Branch W02)** pada repositori `LMS-Broken` dan mengirimkan Pull Request ke asisten dosen.

---

## 2. Hasil Eksplorasi (Read - Break - Fix - Build)

### READ — Telusuri satu request penuh (30 menit)

Ambil route `/tentang` yang Anda buat minggu lalu. Tanpa AI, tulis di catatan Anda:

1. **Baris mana di `routes/web.php` yang menangkapnya?**
   Ditangkap pada baris route:
   ```php
   Route::get('/tentang', function () {
       return view('tentang');
   });
   ```
   *(Sesuai Konsep 2.1, Laravel membaca `routes/web.php` dari atas ke bawah dan mencocokkan method HTTP `GET` serta URI `/tentang`).*

2. **Kalau ditangani controller, berkas dan method mana?**
   Saat ini masih menggunakan *closure* (fungsi tanpa nama). Jika mengikuti pola MVC pada Konsep 2.1 (*Controller sebagai pelayan yang menerima request dan menyerahkan ke view*), rute ini dapat diarahkan ke berkas `app/Http/Controllers/TentangController.php` pada method `index()` atau `__invoke()`.

3. **View mana yang dikembalikan? Di path apa persisnya?**
   View yang dikembalikan adalah template Blade di path `resources/views/tentang.blade.php`.

4. **Layout apa yang membungkusnya?**
   Pada berkas `tentang.blade.php` bawaan Minggu 1 belum dibungkus oleh layout (masih HTML mandiri). Namun, berdasarkan Konsep 2.1 (*Layout dan Komponen Blade*), kerangka bersama seharusnya dibungkus menggunakan komponen Blade `<x-layout>` yang mengarah ke `resources/views/components/layout.blade.php` agar tag HTML dasar, navbar, dan aset `@vite` tidak perlu disalin-tempel berulang kali.

5. **Jalankan `php artisan route:list --path=tentang`. Cocok dengan analisis Anda?**
   Cocok. Perintah tersebut memverifikasi bahwa route `/tentang` terdaftar dengan method `GET|HEAD`, mengarah ke view `tentang`, dan siap menangkap request dari browser.

---

### BREAK — Analisis Kerusakan

| # | Yang Dirusak | Prediksi Sebelum Mencoba | Yang Dipelajari / Pesan Error Sebenarnya |
|---|--------------|--------------------------|------------------------------------------|
| 1 | Ubah `Route::get` jadi `Route::post` pada route daftar mata kuliah | Tidak bisa dibuka via URL browser | Error **`405 Method Not Allowed`** karena browser mengakses URL via HTTP GET |
| 2 | Ubah nama view di `return view(...)` menjadi yang tidak ada | Laravel tidak menemukan template | **`InvalidArgumentException: View [xxx] not found.`** |
| 3 | Hapus `->name('courses.show')` | Helper `route()` error | **`RouteNotFoundException: Route [courses.show] not defined.`** |
| 4 | Pindahkan `/courses/{id}` ke ATAS `/courses/create` | Halaman create tidak bisa dibuka | Muncul **404 Not Found** karena kata `"create"` tertelan sebagai parameter ID |
| 5 | Ganti `{{ $nama }}` jadi `{!! $nama !!}` berisi `<script>` | Script langsung dieksekusi | **XSS Terjadi**: Browser memunculkan popup alert JavaScript dari input user |
| 6 | Hapus `@vite(...)` dari layout | CSS/JS tidak termuat | Tampilan web hancur/tanpa styling Tailwind/CSS |
| 7 | Hentikan `npm run dev` lalu refresh browser | Aset dev server hilang | Halaman gagal memuat aset Vite lokal |
| 8 | Panggil `route('courses.show')` tanpa parameter | Kurang parameter wajib | **`UrlGenerationException: Missing required parameter`** |

---

### FIX — Laporan Perbaikan Branch W02 (`LMS-Broken`)

Telah ditemukan dan diperbaiki **6 masalah** pada branch `W02`:

#### 1. Urutan Route Saling Menutupi (Route Shadowing)
- **Berkas:** `routes/web.php`
- **Penyebab:** Route `/courses/{id}` diletakkan sebelum `/courses/create`.
- **Dampak:** URL `/courses/create` tertelan sebagai parameter `{id}`, memicu error 404 Not Found.
- **Perbaikan:** Memindahkan `Route::get('/courses/create', ...)` sebelum `Route::get('/courses/{id}', ...)`.

#### 2. Method Hapus Menggunakan GET
- **Berkas:** `routes/web.php` & `resources/views/courses/index.blade.php`
- **Penyebab:** Route hapus memakai `GET /courses/{id}/delete`.
- **Dampak:** Rentan web crawler/pre-fetching browser yang dapat menghapus data secara otomatis tanpa sengaja, serta rentan CSRF.
- **Perbaikan:** Mengubah route menjadi `Route::delete('/courses/{id}', ...)` dan membungkus tombol hapus dalam Form method `DELETE` dengan `@csrf`.

#### 3. Hardcoded URL pada View Index
- **Berkas:** `resources/views/courses/index.blade.php`
- **Penyebab:** Link detail ditulis manual string `/courses/{{ $course['id'] }}`.
- **Dampak:** Rawan broken link jika endpoint URL diubah di kemudian hari.
- **Perbaikan:** Menggunakan helper resmi `{{ route('courses.show', $course['id']) }}`.

#### 4. Hardcoded URL pada Komponen Navbar
- **Berkas:** `resources/views/components/layout.blade.php`
- **Penyebab:** Navigasi ditulis hardcode `<a href="/courses">`.
- **Dampak:** Melanggar konvensi Laravel dan path rusak jika berada di subdirektori.
- **Perbaikan:** Mengubah tautan menjadi `{{ route('courses.index') }}`.

#### 5. Kerentanan Keamanan XSS (Cross-Site Scripting)
- **Berkas:** `resources/views/courses/show.blade.php`
- **Penyebab:** Deskripsi dicetak menggunakan sintaks unescaped `{!! $course['description'] !!}`.
- **Dampak:** Script HTML/JS berbahaya dari input user akan langsung dieksekusi oleh browser.
- **Perbaikan:** Mengganti menjadi sintaks escaping aman Blade `{{ $course['description'] }}`.

#### 6. Pelanggaran Arsitektur MVC (Logika Query di View)
- **Berkas:** `resources/views/courses/index.blade.php` & `app/Http/Controllers/CourseController.php`
- **Penyebab:** Terdapat blok `@php array_filter(...) @endphp` di dalam file Blade view.
- **Dampak:** Melanggar prinsip *Separation of Concerns* (MVC).
- **Perbaikan:** Logika filter dipindahkan ke `CourseController::index()` dan View hanya bertugas me-render data bersih.

---