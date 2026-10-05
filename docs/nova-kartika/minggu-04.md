## READ
1. Method apa yang menerima request? Di controller mana?
   Jawab: method yang menerima request adalah `store()` di `CourseController`, method ini dipanggil saat form tambah mata kuliah dikirim dengan request `POST /courses`

2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?
   Jawab: validasi terjadi sebelum baris pertama method store() dijalankan, di kode ini validasinya dilakukan oleh StoreCourseRequest sebelum request masuk ke CourseController@store. karena sks yang dimasukkan 99 tidak sesuai dengan aturan between:1,4, laravel langsung mengembalikan error dan Course::create() tidak dijalankan

3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?
   Jawab: saat validasi gagal laravel otomatis mengarahkan kembali ke halaman form sebelumnya, jadi tidak perlu menulis `redirect()` sendiri, yang menentukan redirectnya adalah laravel melalui `ValidationException` dan exception handlernya lalu error dan input sebelumnya disimpan lewat session

4. Dari mana @error('sks') mengambil pesannya?
   Jawab: `@error('sks')` mengambil pesan error validasi untuk field `sks` yang disimpan laravel disession setelah validasi gagal. pesannya berasal dari aturan `sks` di `StoreCourseRequest`, yaitu `required`, `integer`, dan `between:1,4`

5. Dari mana old('sks') mengambil nilainya? Berapa lama nilai itu bertahan?
   Jawab: `old('sks')` mengambil nilai sks yang sebelumnya dikirim dari flash session `_old_input` setelah validasi gagal. nilainya cuma bertahan satu request berikutnya, jadi setelah halaman form dibuka, data tersebut akan hilang.

6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.
   Jawab: di DevTools → Application → Cookies, cookie session Laravel bernama `laravel_session`, nama ini berasal dari `config/session.php` karena `SESSION_COOKIE` tidak diatur dan `APP_NAME` yang digunakan adalah `Laravel` sehingga Laravel membuat nama cookie menjadi `laravel_session`

## BREAK



