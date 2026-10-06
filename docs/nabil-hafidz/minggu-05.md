*READ*

1. Saya menjalankan ``php artisan route:list --except-vendor`` dari folder proyek. Hasilnya ada 30 route:

```text
GET|HEAD   /                                       dashboard › routes/web.php:12
GET|HEAD   admin/courses                          admin.courses.index › CourseController@index
POST       admin/courses                          admin.courses.store › CourseController@store
GET|HEAD   admin/courses/create                   admin.courses.create › CourseController@create
GET|HEAD   admin/courses/{course}                 admin.courses.show › CourseController@show
PUT|PATCH  admin/courses/{course}                 admin.courses.update › CourseController@update
DELETE     admin/courses/{course}                 admin.courses.destroy › CourseController@destroy
GET|HEAD   admin/courses/{course}/edit            admin.courses.edit › CourseController@edit
GET|HEAD   admin/users                            admin.users.index › UserController@index
POST       admin/users                            admin.users.store › UserController@store
GET|HEAD   admin/users/create                     admin.users.create › UserController@create
GET|HEAD   admin/users/{user}                     admin.users.show › UserController@show
PUT|PATCH  admin/users/{user}                     admin.users.update › UserController@update
DELETE     admin/users/{user}                     admin.users.destroy › UserController@destroy
GET|HEAD   admin/users/{user}/edit                admin.users.edit › UserController@edit
GET|HEAD   dosen/courses                          dosen.courses.index › CourseController@index
GET|HEAD   dosen/courses/{course}                 dosen.courses.show › CourseController@show
GET|HEAD   dosen/courses/{course}/assignments     dosen.courses.assignments.index › AssignmentController@index
GET|HEAD   dosen/courses/{course}/assignments/{assignment} dosen.courses.assignments.show › AssignmentController@show
GET|HEAD   dosen/courses/{course}/materials       dosen.courses.materials.index › MaterialController@index
GET|HEAD   dosen/materials/{material}             dosen.materials.show › MaterialController@show
GET|HEAD   login/{id}                             login-as › routes/web.php:52
GET|HEAD   logout                                 logout › routes/web.php:60
GET|HEAD   mahasiswa/assignments/{assignment}     mahasiswa.assignments.show › AssignmentController@show
GET|HEAD   mahasiswa/courses                     mahasiswa.courses.index › CourseController@index
GET|HEAD   mahasiswa/courses/{course}             mahasiswa.courses.show › CourseController@show
GET|HEAD   mahasiswa/courses/{course}/assignments mahasiswa.courses.assignments.index › AssignmentController@index
GET|HEAD   mahasiswa/courses/{course}/materials   mahasiswa.courses.materials.index › MaterialController@index
GET|HEAD   mahasiswa/materials/{material}         mahasiswa.materials.show › MaterialController@show
GET|HEAD   tentang                                tentang › routes/web.php:19

Showing [30] routes
```

2. Route yang menerima parameter model saya tandai dengan ``[*]``. ``{course}``, ``{assignment}``, ``{material}``, dan ``{user}`` memakai implicit model binding Laravel. ``login/{id}`` saya tandai ``[†]`` karena parameternya bukan model binding, tetapi nilai ID-nya dipakai untuk mencari model ``User``.

3. Tabel "Daftar Titik Rawan IDOR". Middleware ``web`` menjaga fitur web/session, tetapi bukan pembatas akses berdasarkan role atau kepemilikan. Model binding hanya mencari record berdasarkan ID; ID yang valid bukan bukti bahwa pengguna berhak atas record tersebut.

### Daftar Titik Rawan IDOR

|Route bertanda|Siapa yang seharusnya boleh mengakses|Apa yang saat ini mencegah orang lain?|
|---|---|---|
|``admin/courses/{course}`` [*] — ``GET admin.courses.show``|Admin saja untuk route admin; dosen dan mahasiswa memakai route dengan prefix role masing-masing.|Method ``show`` memanggil ``userCanView``, tetapi tidak menegakkan akses admin saja: dosen pemilik course dan mahasiswa terdaftar juga lolos. Selain itu, ada pengecualian yang mengizinkan tamu pada route ``admin.*`` melihat course.|
|``admin/courses/{course}/edit`` [*], ``PUT/PATCH admin/courses/{course}`` [*], ``DELETE admin/courses/{course}`` [*]|Admin saja.|Belum ada middleware role maupun pemeriksaan otorisasi pada method ``edit``, ``update``, dan ``destroy``. Model binding hanya menghasilkan 404 jika ID tidak ada; ID course milik orang lain tetap bisa dipakai.|
|``admin/users/{user}`` [*] — ``GET admin.users.show``|Admin saja; data akun pengguna tidak semestinya dapat dibuka pengguna lain.|Belum ada middleware role atau pemeriksaan otorisasi di controller. Model binding hanya menangani ID yang tidak ditemukan.|
|``admin/users/{user}/edit`` [*], ``PUT/PATCH admin/users/{user}`` [*], ``DELETE admin/users/{user}`` [*]|Admin saja.|Belum ada middleware role atau pemeriksaan otorisasi di method ``edit``, ``update``, dan ``destroy``. Siapa pun yang dapat mencapai route dapat memilih ID user yang valid.|
|``dosen/courses/{course}`` [*] — ``GET dosen.courses.show``|Dosen yang mengampu course tersebut.|Middleware ``role:dosen`` menolak tamu dan role lain. ``userCanView`` juga memeriksa bahwa ``lecturer_id`` course sama dengan ID dosen yang login.|
|``dosen/courses/{course}/assignments`` [*] — ``GET dosen.courses.assignments.index``|Dosen pengampu course.|Middleware ``role:dosen`` dan ``userCanAccessCourse`` memeriksa kepemilikan course.|
|``dosen/courses/{course}/assignments/{assignment}`` [*] — ``GET dosen.courses.assignments.show``|Dosen pengampu course tempat assignment berada.|Middleware ``role:dosen`` dan pemeriksaan akses ke course milik assignment. ``scopeBindings()`` pada route bersarang juga membatasi assignment agar sesuai dengan course pada URL.|
|``dosen/courses/{course}/materials`` [*] — ``GET dosen.courses.materials.index``|Dosen pengampu course.|Middleware ``role:dosen`` dan ``userCanAccessCourse`` memeriksa kepemilikan course.|
|``dosen/materials/{material}`` [*] — ``GET dosen.materials.show``|Dosen pengampu course yang memiliki material tersebut.|Middleware ``role:dosen`` dan ``userCanAccessCourse`` memeriksa course milik material.|
|``mahasiswa/courses/{course}`` [*] — ``GET mahasiswa.courses.show``|Mahasiswa yang terdaftar pada course tersebut.|Middleware ``role:mahasiswa`` dan ``userCanView`` memeriksa keanggotaan mahasiswa di course.|
|``mahasiswa/assignments/{assignment}`` [*] — ``GET mahasiswa.assignments.show``|Mahasiswa yang terdaftar pada course tempat assignment berada.|Middleware ``role:mahasiswa`` dan ``userCanAccessCourse`` memeriksa keanggotaan pada course milik assignment.|
|``mahasiswa/courses/{course}/assignments`` [*] — ``GET mahasiswa.courses.assignments.index``|Mahasiswa yang terdaftar pada course tersebut.|Middleware ``role:mahasiswa`` dan ``userCanAccessCourse`` memeriksa keanggotaan di course.|
|``mahasiswa/courses/{course}/materials`` [*] — ``GET mahasiswa.courses.materials.index``|Mahasiswa yang terdaftar pada course tersebut.|Middleware ``role:mahasiswa`` dan ``userCanAccessCourse`` memeriksa keanggotaan di course.|
|``mahasiswa/materials/{material}`` [*] — ``GET mahasiswa.materials.show``|Mahasiswa yang terdaftar pada course yang memiliki material tersebut.|Middleware ``role:mahasiswa`` dan ``userCanAccessCourse`` memeriksa keanggotaan pada course milik material.|
|``login/{id}`` [†] — ``GET login-as``|Dalam aplikasi nyata, hanya mekanisme login resmi yang boleh membuat sesi pengguna; pengguna tidak boleh memilih akun berdasarkan ID.|Belum ada autentikasi atau otorisasi. Route mencari ``User`` dari ID yang diberikan lalu langsung menjalankan ``auth()->login($user)``. Jadi siapa pun yang dapat membuka URL dapat mencoba login sebagai akun mana pun yang ID-nya diketahui. Ini route simulasi login dan tidak aman untuk aplikasi produksi.|

4. Dari tabel ini, titik paling jelas untuk diperiksa lagi di minggu 7 adalah route CRUD ``admin`` yang belum memiliki otorisasi dan route simulasi ``login/{id}``. Route dosen/mahasiswa sudah memiliki pemeriksaan role dan akses ke course pada controller; ``scopeBindings()`` juga membantu untuk route assignment yang bersarang.

*BREAK*

|No|Yang dicoba|Yang harus diamati|
|---|---|---|
|1|Login sebagai mahasiswa A. Buka submission milik mahasiswa B dengan mengubah angka di URL|IDOR nyata di aplikasi Anda sendiri|
|2|Buka ``/courses/1/assignments/99`` di mana tugas 99 milik mata kuliah lain|Nested route tanpa scoping|
|3|Aktifkan ``Route::scopeBindings()``, ulangi nomor 2|Bandingkan hasilnya|
|4|Daftarkan middleware di ``app/Http/Kernel.php`` seperti tutorial lama|Berkas itu tidak ada — kenali gejalanya|
|5|Pasang ``role:admin`` pada grup, lalu akses sebagai dosen|403 dari middleware|
|6|Sebagai dosen A, edit mata kuliah milik dosen B (keduanya lolos ``role:dosen``)|Middleware saja tidak cukup|
