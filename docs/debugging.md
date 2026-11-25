# Dokumentasi Debugging KampusLMS — Kelompok A07

Dokumen ini mencatat investigasi dan diagnosis sistematis terhadap bug nyata yang dihadapi selama pengembangan KampusLMS (Laravel 12), sesuai instruksi BUILD Minggu 14. Setiap catatan mencakup:
1. **Gejala** yang teramati pada aplikasi.
2. **Cara menemukan dan mempersempit penyebabnya** (metode diagnosis).
3. **Solusi perbaikan** pada basis kode.
4. **Test otomatis pembukti** yang menjamin bug tersebut tidak akan terulang kembali (*regression testing*).

---

## Bug 1: Tugas Berstatus `draft` Bocor dan Dapat Diakses oleh Mahasiswa

### 1. Gejala
Dosen sedang menyusun draf penugasan (`status = 'draft'`) yang belum siap diumumkan. Namun, mahasiswa yang mengetahui atau menebak ID tugas dapat membuka detail instruksi serta mengunduh lampiran soal tugas tersebut melalui URL web `/courses/{course}/assignments/{assignment}` maupun endpoint API `/api/v1/assignments/{id}`.

### 2. Cara Menemukan dan Mempersempit Penyebabnya
1. **Reproduksi:** Login sebagai akun mahasiswa, lalu lakukan *direct URL browsing* ke URL penugasan berstatus draft milik mata kuliah yang diikutinya. Halaman detail tugas berhasil terbuka dengan status HTTP `200 OK`.
2. **Mempersempit Layer Otorisasi:**
   * Diperiksa `AssignmentPolicy@view`: ditemukan bahwa Policy hanya memverifikasi apakah mahasiswa terdaftar di mata kuliah terkait (`$course->students()->whereKey($user->id)->exists()`), tanpa memeriksa kolom `status` penugasan.
   * Diperiksa query controller: query tidak menyaring `where('status', 'published')` saat request datang dari mahasiswa.
3. **Kesimpulan:** Terdapat kebocoran otorisasi berbasis status objek. Mahasiswa berhak mengakses mata kuliah, tetapi tidak berhak melihat tugas yang belum berstatus `published`.

### 3. Solusi Perbaikan
1. Memperbarui `AssignmentPolicy@view`:
   * Jika user adalah dosen pengampu atau admin: izinkan melihat tugas berstatus `draft` maupun `published`.
   * Jika user adalah mahasiswa: hanya izinkan jika `$assignment->status === 'published'`. Jika masih `draft`, kembalikan `403 Forbidden`.
2. Memperbarui query di controller dan API agar penyaringan dilakukan sejak level database (`where('status', 'published')`) untuk mahasiswa.

### 4. Test Pembukti Pencegah Regresi
- **Berkas Uji:** `tests/Feature/RoleMiddlewareTest.php` dan `tests/Feature/NotificationEventIntegrationTest.php`
- **Assertion Kunci:**
  Memastikan request mahasiswa ke tugas draft mengembalikan `403 Forbidden`:
  ```php
  $this->actingAs($student)
      ->get(route('mahasiswa.assignments.show', $draftAssignment))
      ->assertForbidden();
  ```
  Serta memastikan transisi draft ke published mendispatch event dengan benar:
  `test_web_transition_from_draft_to_published_dispatches_event()`

---

## Bug 2: Notifikasi Tugas Baru & Nilai Tidak Terkirim Meskipun Event Berhasil Di-dispatch

### 1. Gejala
Dosen mempublikasikan tugas baru atau selesai menilai submission mahasiswa. Menurut alur bisnis, mahasiswa seharusnya menerima notifikasi lonceng (*in-app notification*). Namun, tabel notifikasi tetap kosong dan mahasiswa tidak menerima pemberitahuan apa pun di antarmuka, meskipun tidak ada error HTTP 500 yang muncul di layar dosen.

### 2. Cara Menemukan dan Mempersempit Penyebabnya
1. **Pemeriksaan Database:**
   * Query ke tabel `notifications`: data kosong (`0 rows`).
   * Query ke tabel `jobs`: ditemukan banyak baris job yang tertahan dan status `attempts` bernilai `0`.
2. **Pemeriksaan Konfigurasi & Stack:**
   * Buka berkas `.env` dan `config/queue.php`: ditemukan `QUEUE_CONNECTION=database`.
   * Listener `SendAssignmentPublishedNotification` dan `SendSubmissionGradedNotification` keduanya mengimplementasikan interface `ShouldQueue`.
   * Di terminal / Docker container, proses `php artisan queue:work` belum dijalankan atau mati di background tanpa proses supervisor.
3. **Kesimpulan:** Event listener berhasil memasukkan antrean ke tabel `jobs` (asinkron), tetapi tidak ada worker aktif yang mengeksekusi antrean tersebut untuk menulis data ke tabel `notifications`.

### 3. Solusi Perbaikan
1. **Lingkungan Pengujian (Testing):** Mengatur `QUEUE_CONNECTION=sync` di berkas `phpunit.xml` agar seluruh event listener langsung dieksekusi seketika (*synchronous*) selama test berjalan, atau menggunakan `Notification::fake()`.
2. **Lingkungan Server Produksi & Docker:**
   * Memperbarui script startup di `Dockerfile` baris 44:
     ```bash
     (php artisan queue:work --sleep=3 --tries=3 &) && exec apache2-foreground
     ```
   * Menghapus flag `--daemon` yang sudah *deprecated* di Laravel 12.

### 4. Test Pembukti Pencegah Regresi
- **Berkas Uji:** `tests/Feature/NotificationFeatureTest.php` dan `tests/Unit/NotificationListenerTest.php`
- **Assertion Kunci:**
  Memastikan notifikasi tersimpan dan dapat dibaca oleh mahasiswa yang bersangkutan:
  ```php
  Notification::fake();
  // ... aksi publish tugas ...
  Notification::assertSentTo($student, NewAssignmentNotification::class);
  ```

---

## Bug 3: Menghapus Tugas atau Mata Kuliah Meninggalkan Berkas Fisik Submission Yatim (Storage Leak)

### 1. Gejala
Saat dosen menghapus tugas kuliah, seluruh data penugasan dan pengumpulan submission di database terhapus bersih karena constraint database `ON DELETE CASCADE`. Namun, kapasitas disk lokal pada `storage/app/private/submissions/` terus bertambah dan berkas PDF submission mahasiswa tetap tertinggal tanpa pemilik (*orphaned files*).

### 2. Cara Menemukan dan Mempersempit Penyebabnya
1. **Langkah Reproduksi:**
   * Mahasiswa mengunggah file `tugas.pdf` ke sebuah penugasan. Sistem menyimpannya dengan nama hash acak di `storage/app/private/submissions/hash_name.pdf`.
   * Dosen menghapus penugasan tersebut melalui tombol hapus di dashboard.
   * Cek database: baris di tabel `assignments` dan `submissions` terhapus sukses.
   * Cek filesystem fisik: file `hash_name.pdf` masih ada di folder `storage/app/private/submissions/`.
2. **Analisis Penyebab:**
   Foreign key `ON DELETE CASCADE` di level MySQL hanya menghapus baris tabel relasi di database engine, tidak memiliki mekanisme untuk menghapus file fisik di storage server. Controller hapus hanya memanggil `$assignment->delete()` tanpa iterasi penghapusan berkas fisik.

### 3. Solusi Perbaikan
Memasang Model Lifecycle Hook pada model `Assignment` dan `Submission`:
1. Pada model `Submission`, daftarkan event `deleting`:
   ```php
   protected static function booted(): void
   {
       static::deleting(function (Submission $submission) {
           if ($submission->file_path && Storage::disk('local')->exists($submission->file_path)) {
               Storage::disk('local')->delete($submission->file_path);
           }
       });
   }
   ```
2. Pada model `Assignment`, sebelum menghapus dirinya sendiri, panggil iterasi `$assignment->submissions->each->delete()` agar model event di setiap submission terpanggil.

### 4. Test Pembukti Pencegah Regresi
- **Berkas Uji:** `tests/Feature/SubmissionFileDeletionTest.php`
- **Assertion Kunci:**
  ```php
  public function test_deleting_submission_removes_its_database_record_and_file(): void
  {
      $submission = $this->createSubmission();
      Storage::disk('local')->put($submission->file_path, 'dummy content');

      $submission->delete();

      $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
      Storage::disk('local')->assertMissing($submission->file_path);
  }
  ```

---

## Bug 4: Endpoint API Pembuatan Tugas Mengembalikan Status HTTP 422 Alih-alih 403 untuk Mahasiswa

### 1. Gejala
Ketika mahasiswa mencoba melakukan request `POST /api/v1/assignments` dengan payload kosong `{}` atau tidak lengkap, server mengembalikan respon status `422 Unprocessable Content` (validasi form gagal), bukan `403 Forbidden`. Hal ini melanggar kontrak spesifikasi API dan prinsip keamanan *authorization first*.

### 2. Cara Menemukan dan Mempersempit Penyebabnya
1. **Hasil Test Suite:** Test `tests/Feature/ApiV1Test.php` gagal pada method `test_students_cannot_create_assignments_and_forbidden_response_matches_contract`:
   ```text
   Expected response status code [403] but received 422.
   Failed asserting that 422 is identical to 403.
   ```
2. **Inspeksi Kode:** Pada `App\Http\Controllers\Api\V1\AssignmentController::store()`, baris pertama langsung memanggil `$data = $request->validate([...])`. Pengecekan izin role baru dilakukan setelah validasi.
3. **Kesimpulan:** Validasi dieksekusi sebelum otorisasi. Pengguna tanpa hak akses (mahasiswa) dapat mengetahui aturan validasi skema input sebelum aksesnya ditolak.

### 3. Solusi Perbaikan
Memindahkan otorisasi ke baris pertama method `store()` sebelum `$request->validate()`:
```php
public function store(Request $request): JsonResponse
{
    Gate::authorize('create', Assignment::class);

    $data = $request->validate([
        'course_id' => ['required', 'integer', 'exists:courses,id'],
        // ...
    ]);
}
```

### 4. Test Pembukti Pencegah Regresi
- **Berkas Uji:** `tests/Feature/ApiV1Test.php`
- **Method Uji:** `test_students_cannot_create_assignments_and_forbidden_response_matches_contract()`
- **Hasil:** Status `403 Forbidden` dikembalikan secara konsisten sebelum validasi input dijalankan.
