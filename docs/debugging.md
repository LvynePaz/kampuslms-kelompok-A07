Debugging — KampusLMS (Kelompok A07)

Dokumen ini mencatat **tiga bug nyata** yang ditemui dan diperbaiki kelompok A07 selama pengembangan KampusLMS semester ini, meliputi gejala yang muncul, proses mempersempit penyebab akar (*root cause*), perbaikan yang diterapkan, serta automated test yang mencegah bug tersebut terulang.

Bug 1: Tugas Berstatus `draft` Bocor dan Dapat Diakses Mahasiswa

- **Referensi Perbaikan:** Commit `d3519e4` (`fix(assignment): sembunyikan tugas draft dari mahasiswa di daftar dan detail tugas MK`)
- **Berkas Terkait:**
  - `app/Http/Controllers/CourseController.php` (`show`)
  - `app/Http/Controllers/AssignmentController.php` (`index`)
  - `app/Http/Controllers/Api/V1/CourseController.php` (`assignments`)
  - `app/Policies/AssignmentPolicy.php` (`view`)

1. Gejala
- Mahasiswa yang terdaftar di sebuah mata kuliah tetap melihat tugas yang statusnya masih `draft` pada halaman detail mata kuliah (`mahasiswa.courses.show`) maupun endpoint daftar tugas web/API.
- Ketika mahasiswa mengakses langsung URL detail tugas berstatus `draft` (`mahasiswa.assignments.show`), sistem tidak menolak permintaan tersebut.

2. Cara Menemukan Penyebabnya
1. **Memeriksa respons & query controller:** Pada `CourseController@show`, pemanggilan `$course->load(['lecturer', 'materials.uploader', 'assignments', 'students'])` memuat seluruh relasi `assignments` tanpa memfilter kolom `status` untuk pengguna dengan `role === 'mahasiswa'`. Pola yang sama ditemukan pada `AssignmentController@index` dan `Api\V1\CourseController@assignments`.
2. **Memeriksa layer otorisasi Policy:** Saat memeriksa `AssignmentPolicy@view`, aturan akses mahasiswa hanya mengecek apakah mahasiswa terdaftar di mata kuliah terkait (`$assignment->course->students()->whereKey($user->id)->exists()`), tanpa memeriksa apakah `$assignment->status === 'published'`.

3. Perbaikan
- Menambahkan filter `where('status', 'published')` khusus untuk role `mahasiswa` pada eager loading di `CourseController@show` serta query di `AssignmentController@index` dan `Api\V1\CourseController@assignments`.
- Memperketat `AssignmentPolicy@view` agar mahasiswa hanya diizinkan melihat tugas jika terdaftar di kelas **dan** `$assignment->status === 'published'`.

4. Test yang Mencegahnya Kembali
- **`tests/Feature/RoleMiddlewareTest.php`** — method `test_student_cannot_see_or_view_draft_assignment`:
  - Memastikan tugas `published` muncul (`assertSee`) dan tugas `draft` tidak muncul (`assertDontSee`) di `route('mahasiswa.courses.show', $course)`.
  - Memastikan akses langsung ke `route('mahasiswa.assignments.show', $draft)` ditolak dengan status `403 Forbidden` (`assertForbidden()`).

Bug 2: Notifikasi Tugas Baru & Nilai Tidak Terkirim Meskipun Event Sudah Di-dispatch

- **Referensi Perbaikan:** Commit `50bb414` (`fix(notif): daftarkan EventServiceProvider agar notifikasi tugas dan nilai terkirim`)
- **Berkas Terkait:**
  - `app/Providers/EventServiceProvider.php`
  - `bootstrap/app.php`
  - `app/Events/AssignmentPublished.php`, `app/Events/SubmissionGraded.php`
  - `app/Listeners/SendAssignmentPublishedNotification.php`, `app/Listeners/SendSubmissionGradedNotification.php`

1. Gejala
- Ketika dosen memublikasikan tugas baru atau menyimpan nilai submission mahasiswa, request web/API berjalan normal tanpa error, tetapi notifikasi tidak pernah masuk ke tabel `notifications` maupun antrean tabel `jobs`.

2. Cara Menemukan Penyebabnya
1. **Memastikan event benar-benar dipicu:** Penelusuran pada `GradeController@store` (`SubmissionGraded::dispatch($submission)`) dan `AssignmentController` menunjukkan event sudah dipanggil saat data valid tersimpan.
2. **Mempersempit ke mekanisme dispatch-ke-listener Laravel 12:** Setelah memastikan tidak ada exception pada log, pemeriksaan diarahkan ke `bootstrap/app.php` dan `app/Providers/`. Ditemukan bahwa belum ada `EventServiceProvider` yang memetakan `$listen` dari `AssignmentPublished` ke `SendAssignmentPublishedNotification` serta `SubmissionGraded` ke `SendSubmissionGradedNotification`, dan provider tersebut juga belum didaftarkan di `->withProviders([...])` dalam `bootstrap/app.php`.

3. Perbaikan
- Membuat `App\Providers\EventServiceProvider` yang mendefinisikan pemetaan `$listen` untuk `AssignmentPublished` dan `SubmissionGraded`.
- Mendaftarkan `App\Providers\EventServiceProvider::class` pada `->withProviders([...])` di `bootstrap/app.php`.

4. Test yang Mencegahnya Kembali
- **`tests/Feature/NotificationEventIntegrationTest.php`**: Memverifikasi `AssignmentPublished` dan `SubmissionGraded` benar-benar di-dispatch dari endpoint web dan API saat kondisi valid (dan tidak di-dispatch saat edit tugas yang sudah `published` atau saat validasi gagal).
- **`tests/Unit/NotificationListenerTest.php`**: Memverifikasi `SendAssignmentPublishedNotification` hanya mengirim ke mahasiswa terdaftar dan `SendSubmissionGradedNotification` hanya mengirim ke pemilik submission.
- **`tests/Feature/NotificationFeatureTest.php`**: Memverifikasi kotak masuk notifikasi, polling `unread_count`, `markAsRead`, serta proteksi IDOR (`404`).

Bug 3: Menghapus Tugas atau Mata Kuliah Meninggalkan Berkas Fisik Submission Yatim

- **Referensi Perbaikan:** Commit `0b493ad` (`Perbaiki penghapusan file submission`)
- **Berkas Terkait:**
  - `app/Models/Submission.php` (`booted` → `static::deleting`)
  - `app/Http/Controllers/AssignmentController.php` (`destroy`)
  - `app/Http/Controllers/Api/V1/AssignmentController.php` (`destroy`)
  - `app/Http/Controllers/CourseController.php` (`destroy`)

1. Gejala
- Saat dosen menghapus tugas (`Assignment`) atau admin menghapus mata kuliah (`Course`), baris data di tabel `submissions` ikut terhapus oleh database, tetapi berkas fisik jawaban mahasiswa di `storage/app/private/submissions/...` tetap tertinggal di disk (*orphan files*).

2. Cara Menemukan Penyebabnya
1. **Memeriksa migrasi dan efek `ON DELETE CASCADE`:** Pada `database/migrations/2026_09_14_000050_create_submissions_table.php`, `assignment_id` menggunakan `cascadeOnDelete()`. Penghapusan cascade di level SQL menghapus baris `submissions` secara langsung tanpa memicu Eloquent model event `deleting` pada tiap objek `Submission`.
2. **Memeriksa controller penghapusan parent:** Di `AssignmentController@destroy` dan `CourseController@destroy`, kode sebelumnya langsung memanggil `$assignment->delete()` atau `$course->delete()` tanpa melakukan iterasi penghapusan pada relasi `submissions`, dan model `Submission` belum memiliki hook penghapusan berkas fisik.

3. Perbaikan
- Menambahkan event hook `static::deleting(...)` di `App\Models\Submission` untuk menghapus berkas fisik dari disk privat `'local'` (serta melempar `RuntimeException` jika penghapusan berkas gagal agar record database tidak terhapus sepihak).
- Membungkus proses penghapusan di `CourseController@destroy`, `AssignmentController@destroy`, dan `Api\V1\AssignmentController@destroy` ke dalam `DB::transaction(...)` sambil memanggil `$submission->delete()` pada setiap submission turunan sebelum parent dihapus.

4. Test yang Mencegahnya Kembali
- **`tests/Feature/SubmissionFileDeletionTest.php`**:
  - `test_deleting_submission_removes_its_database_record_and_file`: memastikan hapus `Submission` menghapus record DB dan file di disk `'local'`.
  - `test_submission_record_is_not_deleted_when_file_deletion_fails`: memverifikasi jika penghapusan file gagal, transaksi/aksi dibatalkan (`RuntimeException`) dan record DB tetap ada.
  - `test_web_assignment_deletion_removes_submission_records_and_files` & `test_api_assignment_deletion_removes_submission_records_and_files`: memastikan hapus tugas lewat web maupun API membersihkan semua submission dan berkas fisiknya.
  - `test_course_deletion_removes_all_descendant_submission_records_and_files`: memastikan hapus mata kuliah membersihkan seluruh assignment, submission, dan berkas fisik turunannya.
