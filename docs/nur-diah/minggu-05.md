## READ
1. Perintah `php artisan route:list --except-vendor` digunakan untuk melihat seluruh route yang terdaftar di aplikasi Laravel. Dari hasil route tersebut dapat diketahui method HTTP, URI, nama route, dan controller atau action yang digunakan. Hasil ini membantu untuk mengetahui struktur navigasi aplikasi dan melihat route mana yang memiliki parameter serta perlu diperhatikan dari sisi keamanan.

| Method | URI | Nama Route | Controller & Action |
| :--- | :--- | :--- | :--- |
| `GET\|HEAD` | `/` | `dashboard` | Inline Closure (`routes/web.php:10`) |
| `GET\|HEAD` | `courses` | `courses.index` | `CourseController@index` |
| `POST` | `courses` | `courses.store` | `CourseController@store` |
| `GET\|HEAD` | `courses/create` | `courses.create` | `CourseController@create` |
| `GET\|HEAD` | `courses/{course}` | `courses.show` | `CourseController@show` `⚠️ Parameter` |
| `PUT\|PATCH` | `courses/{course}` | `courses.update` | `CourseController@update` `⚠️ Parameter` |
| `DELETE` | `courses/{course}` | `courses.destroy` | `CourseController@destroy` `⚠️ Parameter` |
| `GET\|HEAD` | `courses/{course}/edit` | `courses.edit` | `CourseController@edit` `⚠️ Parameter` |
| `GET\|HEAD` | `tentang` | `tentang` | Inline Closure (`routes/web.php:17`) |
| `GET\|HEAD` | `users` | `users.index` | `UserController@index` |
| `POST` | `users` | `users.store` | `UserController@store` |
| `GET\|HEAD` | `users/create` | `users.create` | `UserController@create` |
| `GET\|HEAD` | `users/{user}` | `users.show` | `UserController@show` `⚠️ Parameter` |
| `PUT\|PATCH` | `users/{user}` | `users.update` | `UserController@update` `⚠️ Parameter` |
| `DELETE` | `users/{user}` | `users.destroy` | `UserController@destroy` `⚠️️ Parameter` |
| `GET\|HEAD` | `users/{user}/edit` | `users.edit` | `UserController@edit` `⚠️ Parameter` |

route yang benar-benar ada di proyekmu saat ini harus mengikuti hasil route:list. Kalau setelah Minggu 5 kamu sudah menggunakan `Route::resource()`, hasilnya tentu akan lebih banyak.

2. Route yang menerima parameter dapat dikenali dari penggunaan tanda kurung kurawal seperti `{course}` atau `{user}`. 
Berdasarkan hasil `route:list, route` yang menerima parameter adalah:
- `courses/{course} → parameter {course}`
  - `courses.show`
  - `courses.update`
  - `courses.destroy`
  - `courses.edit`
- `users/{user} → parameter {user}`
  - `users.show`
  - `users.update`
  - `users.destroy`
  - `users.edit`  

    Parameter tersebut digunakan Laravel untuk menentukan data course atau user yang akan diproses berdasarkan ID/model yang diberikan pada URL.

3. Siapa yang seharusnya dapat mengakses route tersebut adalah: 
 
| Route | Parameter | Pihak yang Seharusnya Mengakses |
| :--- | :--- | :--- |
| `courses/{course}` | `{course}` | Pengguna yang memiliki hak akses terhadap data mata kuliah |
| `courses/{course}/edit` | `{course}` | Pengguna yang memiliki hak untuk mengedit mata kuliah |
| `courses/{course}` `PUT/PATCH` | `{course}` | Pengguna yang memiliki hak untuk memperbarui mata kuliah |
| `courses/{course}` `DELETE` | `{course}` | Pengguna yang memiliki hak untuk menghapus mata kuliah |
| `users/{user}` | `{user}` | Admin atau pengguna yang memiliki hak mengelola data pengguna |
| `users/{user}/edit` | `{user}` | Admin atau pengguna yang memiliki hak mengedit pengguna |
| `users/{user}` `PUT/PATCH` | `{user}` | Admin atau pengguna yang memiliki hak memperbarui pengguna |
| `users/{user}` `DELETE` | `{user}` | Admin atau pengguna yang memiliki hak menghapus pengguna |  

4. Daftar Titik Rawan IDOR

| ID | Route / URI | Tingkat Risiko | Potensi Bahaya / Skenario Masalah | Solusi Penanganan (Minggu 5 & 7) |
| :---: | :--- | :---: | :--- | :--- |
| **1** | `GET /users/{user}` | **TINGGI** | Mahasiswa/User biasa mengintip detail profil user lain (termasuk milik Admin) hanya dengan menebak ID. | Bungkus grup route dengan Middleware `role:admin` ATAU cek kepemilikan akun `auth()->id() === $user->id`. |
| **2** | `PUT/DELETE /users/{user}` | **SANGAT TINGGI (KRITIS)** | User biasa dapat mengubah password, data pribadi, atau menghapus akun milik user lain lewat URL/API. | Batasi akses ubah/hapus user hanya untuk Admin via Middleware & Policy. |
| **3** | `GET /courses/{course}/edit` | **SEDANG** | Dosen A dapat membuka formulir edit mata kuliah milik Dosen B hanya dengan mengganti angka ID di URL. | Terapkan pengecekan `abort_unless($course->lecturer_id === auth()->id(), 403)` atau gunakan **Policy** di Minggu 7. |
| **4** | `PUT/DELETE /courses/{course}` | **TINGGI** | Dosen A bisa mengubah isi atau bahkan menghapus mata kuliah yang diampu oleh Dosen B. | Berikan batasan edit/hapus hanya kepada pengampu (`lecturer_id`) atau Admin. |

## BREAK

| # | Yang dicoba | Yang harus Anda amati | Prediksi | Yang saya pelajari |
|---|---|---|---|---|
| 1 | Login sebagai mahasiswa A. Buka submission milik mahasiswa B dengan mengubah angka di URL | IDOR nyata di aplikasi Anda sendiri | Jika controller hanya menggunakan route model binding seperti `Submission $submission` tanpa memeriksa hak akses, mahasiswa A tetap bisa melihat submission milik mahasiswa B selama ID tersebut ada | Route model binding hanya memastikan objek dengan ID tersebut ditemukan, bukan memastikan pengguna berhak mengaksesnya. Jadi `/submissions/41` dan `/submissions/42` sama-sama dapat ditemukan meskipun keduanya milik mahasiswa berbeda. Keamanan akses per objek tetap harus diperiksa dengan authorization/scoping. |
| 2 | Buka `/courses/1/assignments/99` di mana tugas 99 milik mata kuliah lain | Nested route tanpa scoping | Jika nested route tidak menggunakan `scopeBindings()`, Laravel dapat menemukan `course` ID 1 dan `assignment` ID 99 secara terpisah. Jika assignment 99 memang ada, route dapat tetap berhasil meskipun assignment tersebut sebenarnya milik course lain | Nested route belum tentu menjamin hubungan antar-model. Laravel dapat menemukan masing-masing model berdasarkan ID tanpa otomatis memastikan bahwa assignment tersebut merupakan milik course pada URL. Karena itu, hubungan parent-child perlu diamankan dengan scoped binding. |
| 3 | Aktifkan `Route::scopeBindings()`, lalu ulangi nomor 2 | Bandingkan hasilnya dengan nomor 2 | Jika assignment 99 bukan milik course 1, Laravel tidak akan menemukan pasangan yang valid dan request akan menghasilkan 404 Not Found | `scopeBindings()` membuat Laravel melakukan model binding berdasarkan hubungan model induk dan model anak. Jadi `{assignment}` harus benar-benar berada dalam relasi `{course}` yang ada di URL. Ini membantu mencegah akses ke resource dari parent yang salah. |
| 4 | Daftarkan middleware di `app/Http/Kernel.php` seperti tutorial lama | Berkas tersebut tidak ada pada proyek Laravel 12 | Akan gagal karena `app/Http/Kernel.php` memang tidak tersedia pada struktur Laravel 12 yang digunakan. Jika mencoba mengikuti tutorial lama secara persis, Anda akan mencari atau mengedit file yang tidak ada | Laravel 12 menggunakan pendekatan baru untuk konfigurasi middleware. Pendaftaran alias middleware dilakukan melalui `bootstrap/app.php`, bukan `app/Http/Kernel.php`. Jadi sebelum mengikuti tutorial Laravel, saya harus memastikan tutorial tersebut sesuai dengan versi Laravel yang saya gunakan. |
| 5 | Pasang `role:admin` pada grup, lalu akses sebagai dosen | 403 Forbidden dari middleware | Karena pengguna sudah login tetapi role-nya `dosen`, bukan `admin`, middleware `role:admin` akan menghentikan request sebelum mencapai controller dan menghasilkan 403 Forbidden | Middleware dapat digunakan untuk membatasi siapa yang boleh masuk ke suatu area aplikasi. Dalam kasus ini, hanya user dengan role `admin` yang dapat melewati middleware. Pengguna yang sudah login tetapi tidak memiliki role yang diperlukan berbeda dengan pengguna yang belum login: yang pertama umumnya mendapat 403, sedangkan akses yang membutuhkan autentikasi dapat menghasilkan 401/redirect login sesuai konfigurasi aplikasi. |
| 6 | Sebagai dosen A, edit mata kuliah milik dosen B, sementara keduanya sama-sama lolos `role:dosen` | **Middleware saja tidak cukup** | Middleware `role:dosen` akan meloloskan dosen A karena role-nya memang `dosen`. Jika controller tidak melakukan pengecekan kepemilikan, dosen A masih dapat mengedit course milik dosen B | Middleware dan Policy memiliki tanggung jawab yang berbeda. Middleware menjawab, *"Apakah user ini boleh masuk ke area dosen?"*, sedangkan authorization/Policy menjawab, *"Apakah dosen ini boleh melakukan aksi terhadap data tertentu?"* Jadi lolos `role:dosen` tidak otomatis berarti boleh mengubah semua course dosen lain. |

## CHECKPOINT MINGGU 5

- [✓] Apa itu IDOR? Peragakan satu contoh di aplikasi Anda, lalu tunjukkan perbaikannya.
- [✓] Kenapa mengganti ID berurutan dengan UUID **bukan** perbaikan IDOR?
- [✓] Route model binding menjamin apa, dan **tidak** menjamin apa?
- [✓] Apa yang dilakukan `Route::scopeBindings()`? Beri contoh URL yang lolos tanpa itu.
- [✓] Di berkas mana middleware didaftarkan pada Laravel 12? Kenapa berbeda dari kebanyakan tutorial?
- [✓] Kenapa middleware `role:dosen` tidak cukup untuk mencegah dosen A mengedit mata kuliah dosen B?