## READ
1. Setelah menjalankan `php artisan install:api`, Laravel berhasil menyiapkan kebutuhan untuk API. Pada `bootstrap/app.php` terdapat modifikasi agar route API dari `routes/api.php` dapat digunakan dengan `prefix /api`. Selain itu, terdapat beberapa perubahan lain pada project, yaitu penambahan `routes/api.php`, `config/sanctum.php`, `migration create_personal_access_tokens_table.php`, serta perubahan pada `composer.json` dan `composer.lock`. Sanctum juga berhasil dipasang dan migration tabel token berhasil dijalankan.

2. Endpoint `GET /api/v1/courses` digunakan untuk mengambil data mata kuliah melalui API. Berbeda dengan route web yang menampilkan halaman, endpoint API akan memberikan response dalam bentuk JSON. Endpoint ini menjadi jalur bagi client untuk meminta data courses dari aplikasi.

3. **Yang sama:**
   - Keduanya sama-sama berhubungan dengan data Course.
   - Keduanya mengambil dan mengolah data mata kuliah dari database.
   - Keduanya menggunakan backend Laravel. 
 
    **Yang berbeda:**
   - CourseController versi web digunakan untuk menampilkan HTML/Blade kepada pengguna.
   - Endpoint API digunakan untuk memberikan data JSON kepada client.
   - API sebaiknya menggunakan API Resource agar hanya data yang diperlukan yang dikirimkan.
   - Route web berada di routes/web.php, sedangkan route API berada di routes/api.php.

4. Tanpa header `Accept: application/json`, Laravel tidak mendapatkan informasi bahwa client mengharapkan response JSON. Berdasarkan materi, terutama ketika terjadi error, Laravel dapat memberikan response HTML. Namun, ketika menggunakan `Accept: application/json` Laravel mengetahui bahwa client mengharapkan response JSON, sehingga response error dari API juga dapat diberikan dalam format JSON.

5. Hasil `php artisan route:list --path=api` menunjukkan bahwa terdapat **16 route API** dengan prefix `/api/v1`. Jika dibandingkan dengan kontrak API, endpoint yang diwajibkan pada spesifikasi **sudah terdaftar**, yaitu login, logout, profil pengguna, courses, assignments, submissions, grading, dan notifications.

   Beberapa kecocokan yang terlihat adalah:

   - `POST api/v1/auth/login` → sesuai dengan `POST /auth/login`
   - `POST api/v1/auth/logout` → sesuai dengan `POST /auth/logout`
   - `GET api/v1/me` → sesuai dengan `GET /me`
   - `GET api/v1/courses` → sesuai dengan `GET /courses`
   - `GET api/v1/courses/{id}` → sesuai dengan `GET /courses/{id}`
   - `GET api/v1/courses/{id}/materials` → sesuai dengan `GET /courses/{id}/materials`
   - `GET api/v1/courses/{id}/assignments` → sesuai dengan `GET /courses/{id}/assignments`
   - `POST api/v1/assignments` → sesuai dengan `POST /assignments`
   - `PUT/PATCH api/v1/assignments/{id}` → sesuai dengan kontrak
   - `DELETE api/v1/assignments/{id}` → sesuai dengan kontrak
   - `GET api/v1/assignments/{id}/submissions` → sesuai dengan kontrak
   - `POST api/v1/assignments/{assignmentId}/submissions` → secara fungsi sesuai, tetapi nama parameternya berbeda dari kontrak yang menggunakan `{id}`
   - `PUT api/v1/submissions/{id}/grade` → sesuai dengan kontrak
   - `GET api/v1/notifications` → sesuai dengan kontrak
   - `POST api/v1/notifications/{id}/read` → sesuai dengan kontrak

   Jadi, **dari sisi method dan endpoint, route API yang ada sudah mencakup seluruh endpoint dalam kontrak**. Namun, `route:list` hanya menunjukkan bahwa route tersebut **terdaftar**, belum membuktikan bahwa aturan akses seperti `auth`, role, ownership/scope, format response, dan status code sudah benar-benar diterapkan.
   
   `php artisan route:list --path=api` dapat digunakan untuk membandingkan route yang sudah dibuat dengan kontrak API. Dari hasil pengamatan saya, seluruh endpoint yang diwajibkan sudah tersedia, tetapi pengecekan route saja belum cukup untuk memastikan implementasi API sudah sepenuhnya sesuai kontrak.

## BREAK

| # | Yang dicoba | Yang harus Anda amati | Prediksi | Yang saya pelajari |
|---|---|---|---|---|
| 1 | Kembalikan `response()->json(User::all())` di satu endpoint uji | **Hash password tampil di layar** | | |
| 2 |  Hapus `auth:sanctum` dari grup route, panggil tanpa token | Data terbuka untuk publik | | |
| 3 | Panggil endpoint terlindungi dengan token yang sudah dihapus | 401 | | |
| 4 | Login sebagai mahasiswa, panggil `POST /api/v1/assignments` | Berkas tersebut tidak ada pada proyek Laravel 12 | Harus 403, bukan 401 — periksa punya Anda | | |
| 5 |Hapus eager loading, panggil daftar mata kuliah, lihat Telescope/Debugbar |  N+1 di API | | |
| 6 | Hapus `throttle` dari login, jalankan 50 percobaan berturut-turut | Brute force tanpa hambatan | | |
| 7 | Buat pesan login berbeda untuk email salah vs password salah | *User enumeration* — kenapa ini berbahaya | | |

## Checkpoint Minggu 6

- [ ] Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.
- [ ] Apa beda 401 dan 403? Tunjukkan di API Anda satu contoh masing-masing.
- [ ] Kenapa `routes/api.php` tidak ada secara default di Laravel 12? Bagaimana mengaktifkannya?
- [ ] Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?
- [ ] Kenapa pesan gagal login tidak boleh membedakan email salah dan password salah?
- [ ] Kenapa endpoint login wajib di-*throttle*? Berapa nilai yang Anda pakai dan mengapa?