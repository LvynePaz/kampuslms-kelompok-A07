*READ*

1. Saya menjalankan ``php artisan install:api``. Laravel memasang ``laravel/sanctum`` dan menambahkan dependency-nya ke ``composer.json`` serta ``composer.lock``. Laravel juga membuat ``routes/api.php``, ``config/sanctum.php``, dan migrasi ``personal_access_tokens``; migrasi tersebut langsung dijalankan.

   Pada ``bootstrap/app.php``, perubahan utamanya adalah penambahan:

   ```php
   api: __DIR__.'/../routes/api.php',
   ```

   di dalam ``withRouting()``. Baris ini mendaftarkan file ``routes/api.php`` sebagai route API. Laravel memberi prefix ``/api`` secara otomatis dan memakai middleware group ``api``. Karena file yang dibuat ``install:api`` sudah didaftarkan, route ``GET /api/v1/courses`` dibuat di file tersebut tanpa menulis prefix ``/api`` lagi.

   Scaffold juga membuat ``GET /api/user`` dengan middleware ``auth:sanctum``. Route daftar course yang saya buat belum memakai autentikasi. Saya belum menambahkan trait ``HasApiTokens`` ke model ``User`` karena endpoint ini belum menggunakan token Sanctum.

2. Saya menambahkan route ``GET /api/v1/courses`` di ``routes/api.php``. Kontrak sederhana endpoint ini:

   - Status sukses: ``200 OK``.
   - Content-Type: ``application/json``.
   - Bentuk respons: objek dengan key ``data`` berisi array course.
   - Setiap item berisi ``id``, ``code``, ``name``, ``description``, ``sks``, dan ``status``.
   - Daftar diurutkan berdasarkan nama mata kuliah.

   Contoh bentuk respons:

   ```json
   {
     "data": [
       {
         "id": 1,
         "code": "SI2514021",
         "name": "Perencanaan Arsitektur Teknologi Informasi",
         "description": "...",
         "sks": 3,
         "status": "active"
       }
     ]
   }
   ```

   Saya tidak menemukan spesifikasi API terpisah di proyek, jadi kontrak di atas menjadi kontrak yang dipakai untuk endpoint latihan ini. Endpoint ini masih contoh sederhana dan terbuka tanpa login; jangan anggap sudah memiliki pembatasan akses untuk aplikasi produksi.

3. Perbandingan endpoint API dengan ``CourseController@index`` versi web:

   |Yang dibandingkan|Sama|Berbeda|
   |---|---|---|
   |Sumber data|Keduanya mengambil data dari model ``Course`` dan tabel mata kuliah.|Tidak ada perbedaan sumber data.|
   |Tujuan respons|Keduanya menampilkan daftar mata kuliah.|Web mengembalikan view ``courses.index`` untuk dirender browser; API mengembalikan JSON dengan envelope ``data``.|
   |Data yang diminta|Keduanya menampilkan informasi mata kuliah.|Web melakukan eager load relasi ``lecturer``; API hanya memilih ``id``, ``code``, ``name``, ``description``, ``sks``, dan ``status``.|
   |Filter akses|Controller web memfilter course milik dosen atau course yang diikuti mahasiswa berdasarkan user yang login.|Route API ini tidak memeriksa login, role, dosen pengampu, atau keanggotaan mahasiswa; semua course dikembalikan.|
   |Pencarian dan filter|Controller web mendukung parameter ``q`` dan ``status``.|Endpoint API latihan ini belum mendukung filter tersebut.|
   |Urutan dan jumlah|Keduanya menghasilkan daftar course.|Web memakai urutan terbaru dan pagination 15 item; API mengurutkan berdasarkan nama dan belum memakai pagination.|

   Jadi, bagian yang sama adalah sumber data model ``Course``. Perbedaan pentingnya adalah bentuk respons dan aturan filter: tampilan web menyesuaikan daftar untuk role pengguna, sedangkan API latihan ini memberikan semua course dalam JSON.

4. Saya mencoba memanggil endpoint menggunakan ``curl`` tanpa dan dengan header ``Accept: application/json``:

   ```text
   Tanpa header Accept:
   GET /api/v1/courses
   HTTP/1.1 200 OK
   Content-Type: application/json

   Dengan header Accept:
   GET /api/v1/courses
   Accept: application/json
   HTTP/1.1 200 OK
   Content-Type: application/json
   ```

   Isi JSON keduanya sama, yaitu objek ``data`` berisi daftar course. Untuk endpoint ini header ``Accept`` tidak mengubah hasil sukses karena route secara eksplisit membungkus hasil dengan ``response()->json(...)``. Header ``Accept`` menyatakan format yang diminta client; ia tidak mengubah kode respons route ini.

5. Saya menjalankan ``php artisan route:list --path=api``. Hasilnya:

   ```text
   GET|HEAD       api/user       routes/api.php
   GET|HEAD       api/v1/courses api.v1.courses.index › routes/api.php

   Showing [2] routes
   ```

   Route ``api/v1/courses`` cocok dengan kontrak latihan: method GET, path ``/api/v1/courses``, dan nama route ``api.v1.courses.index``. Route ``api/user`` adalah endpoint tambahan dari scaffold Sanctum dan mensyaratkan autentikasi Sanctum; route tersebut bukan bagian dari kontrak daftar course.

*BREAK*

|No|Yang dicoba|Yang harus diamati|
|---|---|---|
|1|Kembalikan ``response()->json(User::all())`` di satu endpoint uji|Email dan seluruh kolom pengguna tampil di layar. Lalu hapus sementara $hidden dari model User dan ulangi: hash password ikut tampil|
|2|Hapus ``auth:sanctum`` dari grup route, panggil tanpa token|SData terbuka untuk publik|
|3|Panggil endpoint terlindungi dengan token yang sudah dihapus|401|
|4|Login sebagai mahasiswa, panggil ``POST`` ``/api/v1/assignments``|Harus 403, bukan 401 — periksa punya Anda|
|5|Hapus eager loading, panggil daftar mata kuliah, lihat Telescope/Debugbar|N+1 di API|
|6|Hapus ``throttle`` dari login, jalankan 50 percobaan berturut-turut|Brute force tanpa hambatan|
|7|Buat pesan login berbeda untuk email salah vs password salah|User enumeration — kenapa ini berbahaya|