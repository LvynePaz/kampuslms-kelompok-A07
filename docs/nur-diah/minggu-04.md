## READ
1. Request dari form tambah mata kuliah dikirim menggunakan method `POST`. Request tersebut ditangani oleh `CourseController`, tepatnya pada method `store()`. Route yang menghubungkan request tersebut adalah route `courses.store` yang mengarah ke `CourseController@store`.
   

2. Validasi dilakukan sebelum method `store()` dijalankan. Laravel memeriksa aturan yang terdapat pada `Request` terlebih dahulu. Karena nilai SKS 99 tidak memenuhi aturan 1–6, request ditolak dan proses penyimpanan course tidak dilanjutkan. Berikut baris kodenya:
```php
   public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:courses,code',
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id,role,dosen',
            'description' => 'nullable|string',
        ]);
    }
```

3. Jika validasi gagal, Laravel otomatis mengembalikan pengguna ke halaman form sebelumnya. Redirect ini ditangani oleh mekanisme validasi Laravel, bukan dengan menulis `redirect()` secara manual di method `store()`. Laravel juga membawa informasi error dan input sebelumnya agar dapat ditampilkan kembali pada form.

4.` @error('sks')` mengambil pesan error validasi untuk field sks yang dibuat oleh Laravel ketika validasi gagal. Jika nilai SKS tidak sesuai aturan, Laravel menyediakan pesan tersebut sehingga dapat ditampilkan pada bagian form menggunakan`@error('sks')`. Ini terdapat di `resources/views/courses/create.blade.php`
```html
                   @error('sks')
                    <div class="error-message">{{ $message }}</div>
                @enderror
```
5. Diambil pada `resources/views/courses/create.blade.php`
```html
  <div class="form-group">
                <label for="sks">SKS</label>

                <input
                    type="number"
                    id="sks"
                    name="sks"
                    value="{{ old('sks') }}"
                    min="1"
                    placeholder="Contoh: 3"
```
`old('sks')` mengambil nilai SKS yang dikirim pada request sebelumnya ketika validasi gagal. Nilai tersebut digunakan agar input yang sudah dimasukkan pengguna tetap muncul pada form. Data old tersebut hanya dipertahankan untuk request berikutnya sehingga tidak disimpan secara permanen di database.

6. Pada DevTools bagian Application → Cookies, terdapat cookie session Laravel dengan nama `laravel_session` dan `XSRF-TOKEN`. Cookie tersebut digunakan untuk membantu Laravel mengenali session pengguna antar-request.
   

## BREAK

| #  |Yang dicoba|Yang harus Anda amati|Prediksi|Yang saya pelajari|
|---|---|---|---|---|
|1|Hapus `@csrf` dari form, lalu kirim|Error 419 — dan renungkan apa yang dicegahnya|Tanpa token CSRF, Laravel akan menolak request dan menampilkan error karena token yang diharapkan tidak ada di form|Setiap form Laravel wajib membawa token unik per-session. Tanpa itu, middleware VerifyCsrfToken menolak request sebelum sampai ke controller. Ini mencegah situs jahat memasang form tersembunyi yang otomatis submit ke aplikasi Anda memakai cookie session korban yang sedang login (korban tidak sadar, karena browser otomatis mengirim cookie ke domain manapun yang cocok)|
|2|Ganti `$request->validated()` menjadi `$request->all()`, lalu kirim field liar lewat `curl`|Mass assignment kembali terbuka|Karena pakai `all()`, field `id` dan `created_at` yang dikirim manual akan ikut tersimpan sesuai nilai yang dikirim, bukan auto-generate|`all()` mengembalikan seluruh data yang dikirim klien tanpa filter, apa pun namanya. `validated()` hanya mengembalikan field yang eksplisit disebut di `rules()`. Ini kenapa mass assignment berbahaya: penyerang bisa menyisipkan field seperti`role=admin` atau `is_verified=1` kalau kebetulan ada di tabel dan tidak di-guard dengan benar|
|3|Hapus validasi `exists:users,id` pada `lecturer_id`, kirim `lecturer_id=99999`|Data yatim masuk database|Karena tidak ada validasi exists, course akan tersimpan dengan lecturer_id yang tidak nyata, kecuali database punya foreign key constraint yang menolaknya di level DB|Validasi Laravel dan constraint database adalah dua lapis pertahanan terpisah. Kalau salah satu tidak ada, yang lain jadi satu-satunya penjaga. Idealnya keduanya ada: validasi untuk UX (pesan error jelas), FK constraint untuk jaminan integritas data di level terendah.|
|4|Hapus `validasi in:...` pada `status`, kirim `status=superadmin`|Enum jebol|Status akan tersimpan sebagai 'superadmin' apa adanya, karena tidak ada whitelist nilai yang diizinkan|`in:...` adalah whitelist di level aplikasi. Kalau kolomnya string bebas di DB, satu-satunya penjaga adalah rule ini|
|5|Hapus `->withQueryString()`, lakukan pencarian lalu klik halaman 2|Filter hilang — bug klasik|Setelah klik halaman 2, parameter q=basis akan hilang dari URL, dan hasil yang tampil jadi semua data lagi, bukan hasil pencarian|Link pagination Laravel secara default hanya membawa parameter `page`. `withQueryString()` memberi tahu paginator untuk ikut menyertakan semua query string yang aktif saat itu ke setiap link halaman. Tanpa ini, pengguna kehilangan konteks pencarian tiap kali pindah halaman, bug yang sangat umum di aplikasi list+filter|
|6|Ganti `return redirect()` menjadi `return view()` pada `store`, lalu tekan F5 setelah simpan|Data ganda; ini alasan PRG ada|Setelah F5, browser akan menampilkan peringatan resend form. Kalau saya konfirmasi, course yang sama akan tersimpan dua kali di database|URL address bar setelah `return view()` masih tetap URL POST `(/courses)`, bukan berpindah ke URL GET baru. Jadi F5 mengulang request terakhir, yaitu POST tadi, persis dengan body yang sama. Pola PRG menghindari ini dengan memaksa browser "melupakan" bahwa request terakhir adalah POST — setelah redirect, address bar menunjuk ke URL GET, jadi F5 aman|
|7|Hapus `old(...)` dari semua input, lalu kirim form dengan satu kesalahan|Rasakan sendiri sebagai pengguna|Setelah submit gagal, semua field akan kosong lagi, termasuk yang tadi sudah diisi benar sehingga harus diketik ulang semuanya|`old()` adalah satu-satunya jembatan yang mengambil input sebelumnya dari flash session dan mengisi ulang value di HTML. Tanpa ini, Laravel tetap menyimpan input lama di session (`getOldInput()` masih ada), tapi tidak ada kode di view yang membacanya — jadi datanya "ada tapi tidak dipakai". Ini kenapa `old()` disebut keluhan pengguna nomor satu kalau dilupakan: satu kesalahan kecil (misal typo di satu field) memaksa isi ulang seluruh formulir panjang|

## CHECKPOINT MINGGU 4

- [] Kenapa validasi di JavaScript tidak dianggap keamanan? Peragakan cara melewatinya.
- [] Apa yang dikembalikan `$request->validated()` dan kenapa lebih aman daripada `$request->all()`?
- [] Jelaskan pola PRG. Apa yang terjadi kalau `store` mengembalikan view?
- [] Kenapa filter pencarian sebaiknya di query string, bukan session? Beri satu skenario yang rusak kalau dipindah ke session.
- [] Apa fungsi `@csrf`? Serangan apa yang dicegahnya, dan bagaimana serangan itu bekerja?
- [] Kenapa aturan `unique` pada update perlu `ignore()`?