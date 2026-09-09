*READ*
1. Tidak ada yang menangkap /tentang karena route yang di web.php itu mengarah ke /halaman utama
2. tidak ada karna route /tentang belum dibuat
3. view yang dikembalikpaan adalah ```welcome``` di path ```resources/views/welcome.blade.php``` dan route /tentang belum ada
4. layout belum ada di routes, karna route langsung di kembalikan ke view ```welcome```  
5. Ya Cocok ![alt text](image-3.png)

*BREAK*
|No | Yang dirusak | Prediksi Sebelum mencoba | Pesan error Sebenarnya|Yang DIpelajari
|---|--------------|-------------------|----------|-----------------|
1 |Ubah ``Route::get`` menjadi ``Route::post`` pada route daftar mata kuliah|tidak tahu|belum tahu|Method HTTP tidak cocok → 405
2| Ubah nama view di ``return view(...)`` menjadi yang tidak ada|tidak tahu | belum tahu | Exception view not found
3|Hapus ``->name('courses.show')``, lalu muat halaman yang memakai ``route('courses.show')``|tidak tahu|belum tahu|Kenapa nama route wajib|
4|Pindahkan ``/courses/{course}`` ke ATAS ``/courses/create``, lalu buka ``/courses/create``|tidak tahu|belum tahu| Urutan route menentukan|
5|Ganti ``{{ $nama }}`` menjadi ``{!! $nama !!}``, isi $nama dengan ``<script>alert('XSS')</script>``|tidak tahu|belum tahu|XSS nyata di layar Anda sendiri
6|Hapus ``@vite(...)`` dari layout|tidak tahu|belum tahu| Aset tidak termuat
7|Hentikan ``npm run dev`` lalu muat ulang halaman|tidak tahu|belum tahu|Beda dev server vs build
8|Panggil ``route('courses.show')`` tanpa mengirim parameter|tidak tahu|belum tahu |Missing required parameter