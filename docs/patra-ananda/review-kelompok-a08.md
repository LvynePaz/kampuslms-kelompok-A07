  # Laporan Peer Review Kelompok A08 (Minggu 13)
**Reviewer:** Patra Ananda (10241061) - Kelompok A07  
**Target Review:** Kelompok A08 (`https://github.com/putriwhd/kampuslms-kelompok-A08`)  
**Mata Kuliah:** SI2514024 - Pemrograman Web  

---

## 1. Temuan Keamanan (Security Review)

- **Lokasi:** `app/Http/Controllers/EnrollmentController.php:81-91` (method `students`) dan rute `routes/web.php:25-27`
- **Kode Terkait:**
  ```php
  public function students(Course $course)
  {
      $students = $course->students()
          ->get(['users.name', 'users.nim_nip'])
          ->map(fn (User $student): array => [
              'name' => $student->name,
              'nim_nip' => $student->nim_nip,
          ]);

      return response()->json($students);
  }
  ```
- **Risiko Masalah:**  
  Rute `/courses/{course}/students` hanya dilindungi middleware `auth` tanpa adanya pengecekan hak akses atau otorisasi Policy (`Gate::authorize`). Siapa pun pengguna yang sudah login (termasuk mahasiswa dari mata kuliah lain atau kelas berbeda) dapat menembak endpoint ini dengan mengubah ID mata kuliah di URL untuk memanen daftar nama dan NIM seluruh mahasiswa yang terdaftar di kelas tersebut. Ini merupakan celah kebocoran data (*Information Disclosure*) dan potensi kerentanan IDOR (*Insecure Direct Object References*).
- **Saran Perbaikan:**  
  Tambahkan otorisasi Gate di awal method agar hanya pihak berhak yang dapat mengakses data mahasiswa kelas tersebut:
  ```php
  public function students(Request $request, Course $course)
  {
      Gate::authorize('view', $course);

      // Pastikan mahasiswa yang melihat memang terdaftar di kelas tersebut
      if ($request->user()->role === 'mahasiswa') {
          abort_unless($course->students()->whereKey($request->user()->id)->exists(), 403);
      }

      $students = $course->students()
          ->get(['users.name', 'users.nim_nip']);

      return response()->json($students);
  }
  ```

---

## 2. Temuan Performa (Performance Review - N+1 Query in Loop)

- **Lokasi:** `app/Http/Controllers/CourseController.php:246-254` (method `updateStudents`)
- **Kode Terkait:**
  ```php
  foreach ($newStudentIds as $studentId) {
      $student = User::findOrFail($studentId);

      $currentSks = $student->courses()
          ->where('courses.status', 'active')
          ->sum('courses.sks');

      if (($currentSks + $course->sks) > EnrollmentController::MAX_SKS_LIMIT) {
          ...
      }
  }
  ```
- **Risiko Masalah:**  
  Di dalam perulangan `$newStudentIds`, terjadi eksekusi query database berulang secara berantai untuk setiap mahasiswa:
  1. `User::findOrFail($studentId)` (menembak 1 query `SELECT * FROM users WHERE id = ?`).
  2. `$student->courses()->sum('courses.sks')` (menembak 1 query agregasi `SELECT SUM(sks) FROM courses ...`).  
  Jika Admin mendaftarkan 25 mahasiswa sekaligus, sistem akan mengeksekusi 50 query berturut-turut ke database dalam satu siklus request (*N+1 query problem / query in loop*). Hal ini membuat proses pendaftaran massal menjadi lambat dan berisiko memicu database bottleneck saat traffic tinggi.
- **Saran Perbaikan:**  
  Tarik data seluruh mahasiswa yang akan didaftarkan sekaligus di luar perulangan menggunakan eager loading relasi `courses`:
  ```php
  $students = User::with(['courses' => fn ($q) => $q->where('courses.status', 'active')])
      ->whereIn('id', $newStudentIds)
      ->get();

  foreach ($students as $student) {
      $currentSks = (int) $student->courses->sum('sks');

      if (($currentSks + $course->sks) > EnrollmentController::MAX_SKS_LIMIT) {
          return back()
              ->withErrors([
                  'student_ids' => "Mahasiswa {$student->name} akan melebihi batas SKS maksimal ("
                      .EnrollmentController::MAX_SKS_LIMIT.' SKS).',
              ])
              ->withInput();
      }
  }
  ```
  Dengan perubahan ini, puluhan query terpangkas menjadi hanya 2 query konstan.

---

## 3. Temuan Struktur & Arsitektur (Code Quality / MVC Architecture)

- **Lokasi:** `app/Http/Controllers/AssignmentController.php:173` (method `submitWeb`)
- **Kode Terkait:**
  ```php
  try {
      (new ApiSubmissionController)->saveSubmission($request, $assignment);
  } catch (ValidationException $e) {
      ...
  }
  ```
- **Risiko Masalah:**  
  Controller web melakukan instansiasi langsung terhadap controller API: `(new ApiSubmissionController)->saveSubmission(...)`.  
  Pola *controller memanggil controller lain* melanggar prinsip *Single Responsibility* dan konvensi MVC Laravel. Hal ini menyebabkan *tight coupling* antara layer web dan layer API, menyulitkan pembuatan *automated feature test*, serta melewati siklus middleware/request lifecycle yang semestinya.
- **Saran Perbaikan:**  
  Pindahkan logika inti penyimpanan file dan pencatatan database ke dalam sebuah Service Class (misalnya `App\Services\SubmissionService`) atau Action Class (misalnya `App\Actions\StoreSubmissionAction`). Baik `AssignmentController` (Web) maupun `ApiSubmissionController` (API) cukup memanggil Service tersebut, sehingga kode dapat digunakan ulang (*reusable*) tanpa saling memanggil antar-controller.
