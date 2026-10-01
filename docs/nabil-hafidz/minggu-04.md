*READ*
1. Method yang menerima request adalah ``store(Request $request)`` pada ``CourseController``. Method ini dipanggil oleh route resource ``courses`` untuk request ``POST /courses``.

2. Validasi terjadi saat ``$request->validate([...])`` dipanggil di awal method ``store``, sebelum ``Course::create($validated)`` dan sebelum mata kuliah disimpan ke database.

3. Laravel melempar ``ValidationException`` ketika validasi gagal. Exception handler Laravel kemudian mengarahkan request kembali ke halaman sebelumnya, yaitu halaman form yang mengirim request. Middleware ``web`` membawa pesan error dan input lama melalui session.

4. ``@error('sks')`` mengambil pesan untuk field ``sks`` dari error validasi yang disimpan Laravel di session dengan key ``errors``. Pesan tersebut berasal dari aturan validasi ``sks``, yaitu ``required|integer|min:1|max:6``.

5. ``old('sks')`` mengambil nilai input ``sks`` dari session flash Laravel setelah request sebelumnya gagal divalidasi. Nilai ini dikirim kembali agar form tetap terisi. Data flash biasanya hanya bertahan untuk request berikutnya, lalu dihapus setelah request tersebut selesai.

6. Cookie session Laravel adalah ``laravel_session``. Pada ``config/session.php``, ``SESSION_COOKIE`` tidak diatur dan ``APP_NAME=Laravel``, sehingga Laravel mengubah nama aplikasi menjadi ``laravel`` lalu menambahkan suffix ``_session``.

*BREAK*

|No|Yang dirusak|Yang harus diamati|
|---|---|---|
|1|Hapus ``@csrf`` dari form, lalu kirim|Error 419 — dan renungkan apa yang dicegahnya|
|2|Ganti ``$request->validated()`` menjadi ``$request->all()``, lalu kirim field liar lewat curl|Mass assignment kembali terbuka
|3|Hapus validasi ``exists:users``,id pada ``lecturer_id``, kirim ``lecturer_id=99999``|Data yatim masuk database|
|4|Hapus validasi in:... pada status, kirim status=superadmin|Enum jebol
|5|Hapus ``->withQueryString()``, lakukan pencarian lalu klik halaman 2|Filter hilang — bug klasik
|6|Ganti ``return redirect()`` menjadi ``return view()`` pada ``store``, lalu tekan F5 setelah simpan|Data ganda; ini alasan PRG ada
|7|Hapus ``old(...)`` dari semua input, lalu kirim form dengan satu kesalahan|Rasakan sendiri sebagai pengguna