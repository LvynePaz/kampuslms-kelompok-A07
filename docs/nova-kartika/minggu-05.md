## READ
1. Jalankan php artisan route:list --except-vendor. Salin keluarannya ke catatan.
   Jawab:

| Method    | URI                                       | Nama Route                            | Controller                   |
|-----------|-------------------------------------------|---------------------------------------|------------------------------|
| GET       | `/`                                       | `dashboard`                           | `routes/web.php`             |
| GET       | `/admin/courses`                          | `admin.courses.index`                 | `CourseController@index`     |
| POST      | `/admin/courses`                          | `admin.courses.store`                 | `CourseController@store`     |
| GET       | `/admin/courses/create`                   | `admin.courses.create`                | `CourseController@create`    |
| GET       | `/admin/courses/{course}`                 | `admin.courses.show`                  | `CourseController@show`      |
| PUT/PATCH | `/admin/courses/{course}`                 | `admin.courses.update`                | `CourseController@update`    |
| DELETE    | `/admin/courses/{course}`                 | `admin.courses.destroy`               | `CourseController@destroy`   |
| GET       | `/admin/courses/{course}/edit`            | `admin.courses.edit`                  | `CourseController@edit`      |
| GET       | `/admin/users`                            | `admin.users.index`                   | `UserController@index`       |
| POST      | `/admin/users`                            | `admin.users.store`                   | `UserController@store`       |
| GET       | `/admin/users/create`                     | `admin.users.create`                  | `UserController@create`      |
| GET       | `/admin/users/{user}`                     | `admin.users.show`                    | `UserController@show`        |
| PUT/PATCH | `/admin/users/{user}`                     | `admin.users.update`                  | `UserController@update`      |
| DELETE    | `/admin/users/{user}`                     | `admin.users.destroy`                 | `UserController@destroy`     |
| GET       | `/admin/users/{user}/edit`                | `admin.users.edit`                    | `UserController@edit`        |
| GET       | `/dosen/assignments/{assignment}`         | `dosen.assignments.show`              | `AssignmentController@show`  |
| GET       | `/dosen/courses`                          | `dosen.courses.index`                 | `CourseController@index`     |
| GET       | `/dosen/courses/{course}`                 | `dosen.courses.show`                  | `CourseController@show`      |
| GET       | `/dosen/courses/{course}/assignments`     | `dosen.courses.assignments.index`     | `AssignmentController@index` |
| GET       | `/dosen/courses/{course}/materials`       | `dosen.courses.materials.index`       | `MaterialController@index`   |
| GET       | `/dosen/materials/{material}`             | `dosen.materials.show`                | `MaterialController@show`    |
| GET       | `/mahasiswa/assignments/{assignment}`     | `mahasiswa.assignments.show`          | `AssignmentController@show`  |
| GET       | `/mahasiswa/courses`                      | `mahasiswa.courses.index`             | `CourseController@index`     |
| GET       | `/mahasiswa/courses/{course}`             | `mahasiswa.courses.show`              | `CourseController@show`      |
| GET       | `/mahasiswa/courses/{course}/assignments` | `mahasiswa.courses.assignments.index` | `AssignmentController@index` |
| GET       | `/mahasiswa/courses/{course}/materials`   | `mahasiswa.courses.materials.index`   | `MaterialController@index`   |
| GET       | `/mahasiswa/materials/{material}`         | `mahasiswa.materials.show`            | `MaterialController@show`    |
| GET       | `/tentang`                                | `tentang`                             | `routes/web.php`             |

2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).
   Jawab: 
   route yg menerima parameter model adalah route yang ada {...} di bagian URLnya, dari route yang saya cek parameter modelnya yaitu:
   - {course} → digunakan di route yang berhubungan sama mata kuliah
   - {user} → digunakan di route yang berhubungan sama pengguna
   - {assignment} → digunakan di route yang berhubungan sama tugas
   - {material} → digunakan di route yang berhubungan sama materi

    route yang memiliki parameter tersebut yaitu:

| Parameter      | Route yang menerima parameter             |
| -------------- | ----------------------------------------- |
| `{course}`     | `/admin/courses/{course}`                 |
| `{course}`     | `/admin/courses/{course}/edit`            |
| `{course}`     | `/dosen/courses/{course}`                 |
| `{course}`     | `/dosen/courses/{course}/assignments`     |
| `{course}`     | `/dosen/courses/{course}/materials`       |
| `{course}`     | `/mahasiswa/courses/{course}`             |
| `{course}`     | `/mahasiswa/courses/{course}/assignments` |
| `{course}`     | `/mahasiswa/courses/{course}/materials`   |
| `{user}`       | `/admin/users/{user}`                     |
| `{user}`       | `/admin/users/{user}/edit`                |
| `{assignment}` | `/dosen/assignments/{assignment}`         |
| `{assignment}` | `/mahasiswa/assignments/{assignment}`     |
| `{material}`   | `/dosen/materials/{material}`             |
| `{material}`   | `/mahasiswa/materials/{material}`         |

3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.
   Jawab:
   untuk yang saat ini mencegah, dari route:list yang saya lihat belum terlihat middlewarenya, jadi saya tulis dari yang bisa diketahui dari route terlebih dahulu

| Route                                     | Yang seharusnya boleh mengakses | Yang saat ini mencegah orang lain                             |
| ----------------------------------------- | ------------------------------- | ------------------------------------------------------------- |
| `/admin/courses/{course}`                 | admin                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/admin/courses/{course}/edit`            | admin                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/admin/users/{user}`                     | admin                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/admin/users/{user}/edit`                | admin                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/dosen/assignments/{assignment}`         | dosen                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/dosen/courses/{course}`                 | dosen                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/dosen/courses/{course}/assignments`     | dosen                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/dosen/courses/{course}/materials`       | dosen                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/dosen/materials/{material}`             | dosen                           | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/mahasiswa/assignments/{assignment}`     | mahasiswa                       | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/mahasiswa/courses/{course}`             | mahasiswa                       | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/mahasiswa/courses/{course}/assignments` | mahasiswa                       | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/mahasiswa/courses/{course}/materials`   | mahasiswa                       | belum bisa dipastiin dari route:list harus cek middlewarenya |
| `/mahasiswa/materials/{material}`         | mahasiswa                       | belum bisa dipastiin dari route:list harus cek middlewarenya |

4. Buat tabel di docs/minggu-05-<nama>.md berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.
   Jawab:
   ## Daftar Titik Rawan IDOR

| No. | Titik Rawan | Kenapa Bisa Rawan IDOR | Yang Perlu Dicek |
|---|---|---|---|
| 1 | submission berdasarkan `{assignment}` | user bisa aja mencoba mengganti ID assignment untuk melihat assignment yang bukan haknya | cek apakah mahasiswa cuma bisa melihat assignment yang boleh diakses |
| 2 | material berdasarkan `{material}` | user bisa mengganti ID material untuk mencoba melihat material lain | cek apakah user cuma bisa melihat material yang sesuai dengan aksesnya |
| 3 | course berdasarkan `{course}` | user bisa mengganti ID course di URL untuk mencoba membuka course lain | cek apakah user memang punya hak mengakses course tsb |
| 4 | user berdasarkan `{user}` | ID user bisa diganti untuk mencoba melihat atau mengubah data user lain | cek apakah cuma admin yang bisa akses data user |
| 5 | assignment berdasarkan `{assignment}` pada dosen | dosen bisa mencoba mengganti ID assignment untuk mengakses assignment yang tidak terkait dengannya | cek apakah assignment sesuai dengan course yang diampu sama dosen |

## BREAK