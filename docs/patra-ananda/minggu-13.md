# Catatan Individu Minggu 13 - Patra Ananda (10241061)
**Mata Kuliah:** SI2514024 - Pemrograman Web  
**Proyek:** KampusLMS (Kelompok A07)  
**Topik:** Kolaborasi, Version Control, dan Peer Review

---

## 13.1 READ - Analisis Riwayat Git Kelompok & Individu

Data diambil langsung menggunakan perintah `git shortlog -sn --all`:
- **Traa897 / Patralysne (Patra Ananda):** 140 commit (103 + 37)
- **10241055-cpu / nabilh4fidz-png / Nabil Barok (Nabil Hafidz):** 66 commit (36 + 20 + 10)
- **winniendah / Indah_59 / Nur Diah Indah (Nur Diah Indah):** 53 commit (32 + 14 + 7)
- **novakrtkaa / Nova Kartika Candra (Nova Kartika):** 33 commit (28 + 5)

### Evaluasi Pertanyaan Subbab 13.3 READ:

1. **Berapa commit atas nama Anda? Bandingkan dengan anggota lain.**  
   Commit atas nama saya tercatat 140 commit. Jumlah ini merupakan porsi terbesar di kelompok karena menangani integrasi alur kerja, perbaikan tampilan global, dan konfigurasi deployment Railway.
2. **Adakah minggu di mana Anda tidak punya commit sama sekali?**  
   Tidak ada. Sejak Minggu 1 hingga Minggu 12, aktivitas commit tercatat aktif setiap minggu di branch `patra_61` dan integrasi ke `main`.
3. **Berapa persen pesan commit yang mengikuti Conventional Commits?**  
   Sekitar 75%. Pada fase awal dan pengerjaan darurat (misal saat debugging CSS dan error submission di minggu 10), masih terdapat commit dengan pesan imperatif non-standar seperti `tambahkan diagnostik error submissions` atau `rapikan tabel nilai`. Setelah minggu 11, format `feat:`, `fix:`, dan `style:` konsisten digunakan.
4. **Temukan satu commit Anda sendiri yang pesannya buruk. Tulis ulang seharusnya bagaimana.**  
   - **Commit lama:** `985e83d tambahkan diagnostik error submissions`
   - **Tulis ulang seharusnya:** `fix(submissions): add logging and error diagnostics for null file uploads`
5. **Commit terbesar (`git log --stat`): Apakah seharusnya dipecah?**  
   - **Commit:** `850089c tambahkan tab navigasi dan kartu tugas mingguan` (2 files changed, 630 insertions, 195 deletions).
   - **Evaluasi:** Ya, seharusnya dipecah menjadi dua commit terpisah: satu commit untuk styling layout kartu tugas mingguan (`feat(ui): add weekly assignment cards`), dan satu commit untuk logika tab navigasi (`feat(courses): add tab navigation component`).

---

## 13.2 BREAK - Percobaan Alur Kerja Git

1. **Commit `.env` palsu dan push:** CI harus menolak berkas `.env`. Jika lolos, aturan `.gitignore` bermasalah.
2. **Hapus `.env` dengan `git rm --cached`:** Berkas hilang dari commit terbaru (HEAD), tetapi tetap tertinggal di riwayat lama Git. Kredensial yang pernah ter-push wajib dianggap bocor dan diganti.
3. **Konflik merge dua anggota:** Terjadi saat dua branch mengubah baris yang sama di file yang sama. Konflik harus diselesaikan dengan memahami logika kedua sisi, bukan memilih buta.
4. **Push langsung ke `main`:** Kemarin sempat terjadi push langsung ke `main` karena Branch Protection belum diaktifkan di GitHub Settings dan akun bertindak sebagai Owner. Ini membuktikan bahwa tanpa Branch Protection, aturan kerja tim mudah terlanggar secara tidak sengaja.
5. **Merge PR yang gagal CI:** Tombol merge wajib dikunci jika pipeline test/build belum hijau.
6. **Kepenulisan Git (`git commit --author="..."`):** Nama author commit di Git bisa dipalsukan secara lokal. Oleh karena itu, penilaian kontribusi tidak boleh hanya berpatokan pada `git shortlog`, melainkan diuji lewat wawancara langsung.

---

## 13.3 BUILD - Peer Review Kelompok A08 (Sesuai Modul Resmi Minggu 13)

Sesuai penugasan modul, dilakukan peer review terhadap basis kode **Kelompok A08** (`https://github.com/putriwhd/kampuslms-kelompok-A08`). Laporan lengkap tersimpan di berkas `docs/patra-ananda/review-kelompok-a08.md`.

### Tiga Temuan Review Resmi (Format: Lokasi, Risiko, Saran):

#### 1. Temuan Keamanan (Security Review - Information Disclosure & IDOR)
- **Lokasi:** `app/Http/Controllers/EnrollmentController.php:81-91` (method `students`) dan rute `routes/web.php:25-27`
- **Risiko:** Rute `GET /courses/{course}/students` hanya dilindungi middleware `auth` tanpa pemanggilan `Gate::authorize()`. Mahasiswa mana pun yang login dapat mengganti ID mata kuliah di URL untuk memanen daftar nama dan NIM mahasiswa yang terdaftar di kelas lain (kebocoran data privasi / celah IDOR).
- **Saran:** Pasang otorisasi Gate di awal method agar hanya mahasiswa terdaftar atau dosen pengampu yang berhak melihat:
  ```php
  Gate::authorize('view', $course);
  if ($request->user()->role === 'mahasiswa') {
      abort_unless($course->students()->whereKey($request->user()->id)->exists(), 403);
  }
  ```

#### 2. Temuan Performa (Performance Review - N+1 Query in Loop)
- **Lokasi:** `app/Http/Controllers/CourseController.php:246-254` (method `updateStudents`)
- **Risiko:** Di dalam perulangan `foreach ($newStudentIds as $studentId)`, controller mengeksekusi dua query database berulang untuk setiap mahasiswa: `User::findOrFail($studentId)` dan `$student->courses()->sum('courses.sks')`. Jika admin mendaftarkan 25 mahasiswa, terjadi 50 query berturut-turut (N+1 query in loop) yang memperlambat respons server saat pendaftaran kelas.
- **Saran:** Tarik data mahasiswa secara massal di luar loop menggunakan eager loading:
  ```php
  $students = User::with(['courses' => fn ($q) => $q->where('courses.status', 'active')])
      ->whereIn('id', $newStudentIds)
      ->get();
  ```
  Dengan ini, puluhan query terpangkas menjadi 2 query efisien.

#### 3. Temuan Struktur & Keterbacaan (Code Quality / MVC Architecture)
- **Lokasi:** `app/Http/Controllers/AssignmentController.php:173` (method `submitWeb`)
- **Risiko:** Controller web melakukan instansiasi langsung terhadap controller API: `(new ApiSubmissionController)->saveSubmission(...)`. Pola *controller memanggil controller lain* melanggar prinsip MVC dan Single Responsibility, menyebabkan tight coupling antara layer web dan API, serta menyulitkan penulisan automated test.
- **Saran:** Ekstrak logika simpan submission ke dalam Service Class khusus (misal: `App\Services\SubmissionService`) yang dapat diinjeksi secara bersih oleh kedua controller tersebut.

---

## 13.5 Checkpoint Minggu 13

- [x] **Berapa commit atas nama Anda? Tunjukkan `git shortlog -sn`.**
  - **Perintah:** `git shortlog -sn HEAD`
  - **Hasil:** 97 `Traa897` + 37 `Patralysne` = **134 commit** (atau 141 commit jika dihitung di seluruh branch `--all`).
  - **Catatan akun:** Akun hanya ada 1. Dua nama muncul karena 97 commit awal dibuat saat konfigurasi Git lokal masih bernama `Traa897` (`patran05534@gmail.com`), kemudian username diperbarui menjadi `Patralysne` (`95853238+LvynePaz@users.noreply.github.com`). Karena Git commit bersifat *immutable*, riwayat nama lama tetap terekam apa adanya.

- [x] **Kenapa `git rm --cached .env` tidak cukup kalau `.env` sudah pernah ter-push?**
  - Perintah `git rm --cached .env` hanya menghapus file dari staging index agar tidak ter-commit ke depan, tetapi riwayat isi file `.env` beserta kredensial rahasianya **masih tersimpan permanen di database commit Git lama**. Siapa pun yang meng-clone repository atau melihat log lama tetap bisa membaca rahasia tersebut.
  - Penanganan wajib: commit lama harus di-rewrite (menggunakan `git filter-repo` atau BFG Repo-Cleaner) dan seluruh kredensial/rahasia yang bocor wajib di-rotate (diganti).

- [x] **Tunjukkan satu temuan review yang Anda tulis. Jelaskan risikonya tanpa membaca catatan.**
  - **Temuan di Kelompok A08 (PR #67):** File `app/Http/Controllers/EnrollmentController.php` baris 75-85 pada method `students(Course $course)` dan route `routes/web.php` baris 25-27.
  - **Risiko Teknis:** Celah IDOR (Insecure Direct Object Reference) dan Broken Access Control. Endpoint `GET /courses/{course}/students` mengembalikan daftar nama dan NIM mahasiswa suatu kelas tanpa otorisasi (`Gate::authorize`). Mahasiswa kelas lain yang sudah login cukup mengubah parameter course di URL untuk melihat daftar mahasiswa kelas lain.
  - **Solusi:** Tambahkan `Gate::authorize('view', $course)` atau batasi akses hanya untuk dosen pengampu dan mahasiswa terdaftar.

- [x] **Tunjukkan satu temuan yang masuk ke kelompok Anda. Anda terima atau tolak? Kenapa?**

  Berikut dua data temuan riil yang masuk ke pull request kelompok A07 (pilih salah satu saat wawancara):

  - **PILIHAN A - TEMUAN YANG DITERIMA:**
    - **Reviewer:** `@syahidrdho`
    - **Tautan PR:** PR #56 (`https://github.com/LvynePaz/kampuslms-kelompok-A07/pull/56#discussion_r4618384060`)
    - **File & Baris:** `routes/api.php` baris 33
    - **Kode Asal:**
      ```php
      Route::get('/users/{id}', function ($id) {
          $user = \App\Models\User::findOrFail($id);
          return new \App\Http\Resources\UserResource($user);
      });
      ```
    - **Status:** **Diterima**.
    - **Alasan Teknis & Tindak Lanjut:** Endpoint detail profil pengguna awalnya ditaruh di luar middleware `auth:sanctum`. Akibatnya, publik/guest tanpa login bisa menebak ID di URL dan memanen profil pengguna lain (celah *information disclosure*). Temuan ini valid dan langsung kami terima: rute dipindahkan ke dalam grup `auth:sanctum` pada commit `6937d0b`, serta diperkuat dengan otorisasi anti-IDOR di mana hanya role admin atau pengguna pemilik akun itu sendiri yang diizinkan memanggil endpoint `GET /api/v1/users/{id}`.

  - **PILIHAN B - TEMUAN YANG DITOLAK:**
    - **Reviewer:** `@SufSoup`
    - **Tautan PR:** PR #44 (`https://github.com/LvynePaz/kampuslms-kelompok-A07/pull/44`)
    - **File & Baris:** `app/Http/Controllers/AssignmentController.php` baris 40-52 (method `submissions`) dan `resources/views/assignments/submissions.blade.php`
    - **Kode Terkait:**
      ```php
      $students = $assignment->course->students()
          ->with(['submissions' => fn ($query) => $query->where('assignment_id', $assignment->id)])
          ->orderBy('name')
          ->get();
      ```
    - **Status:** **Ditolak**.
    - **Alasan Teknis & Solusi Alternatif:** Reviewer menyarankan agar daftar pengumpulan tugas mahasiswa dipecah menggunakan paginasi (`paginate(10)`). Saran paginasi ditolak karena halaman ini berfungsi sebagai dasbor monitoring dosen untuk melihat kelengkapan pengumpulan satu kelas secara utuh (siapa yang sudah dan belum mengumpulkan). Memotong data per 10 orang mempersulit dosen memantau progres kelas. Selain itu, query sudah dioptimasi menggunakan eager loading `with()` (hanya 2 query dieksekusi, bebas N+1), dan jumlah mahasiswa per kelas dibatasi 25-50 orang. Sebagai gantinya, kekhawatiran reviewer diselesaikan dengan menambahkan fitur filter status instan (*Semua*, *Tepat Waktu*, *Terlambat*, *Belum Mengumpulkan*) serta pencarian nama/NIM tanpa memotong kelengkapan data satu kelas.

- [x] **Kenapa PR sebaiknya kecil dan satu fitur?**
  - **Mudah dan cepat direview:** Reviewer tidak mengalami fatigue dan lebih teliti menemukan bug atau celah keamanan.
  - **Minim konflik merge:** Perubahan yang terisolasi memperkecil risiko tabrakan kode dengan pekerjaan anggota lain.
  - **Aman saat rollback:** Jika timbul regresi atau bug di production, PR dapat di-revert secara instan tanpa membatalkan fitur lain yang sudah berjalan baik.

- [x] **Kenapa penilaian kontribusi tidak bisa hanya berdasarkan jumlah commit?**
  - Jumlah commit mudah dimanipulasi (misal commit per baris atau commit perbaikan typo berulang kali).
  - Jumlah commit tidak mencerminkan kualitas kode, pencegahan celah keamanan, maupun bobot arsitektur (1 commit refactor optimasi database atau penutupan celah IDOR jauh lebih berharga daripada 20 commit perbaikan tampilan/spasi).

---

## 13.6 Rangkuman Materi Kuis 13

1. **Alur Branching (Branch Strategy):**
   - `main`: Selalu stabil, siap produksi, tidak menerima push langsung (dilindungi Branch Protection).
   - `dev`: Cabang integrasi mingguan tempat semua fitur digabungkan sebelum masuk ke `main`.
   - `feat/*` dan `fix/*`: Cabang kerja spesifik per fitur atau perbaikan bug, selalu digabungkan melalui Pull Request.
2. **Conventional Commits:**
   - Struktur standar: `<tipe>(<lingkup opsional>): <deskripsi singkat imperative>`.
   - Tipe utama: `feat` (fitur baru), `fix` (perbaikan bug), `refactor` (ubah struktur tanpa ubah perilaku), `docs` (dokumentasi), `test` (pengujian otomatis), `style` (tampilan/formatting).
3. **Resolusi Konflik Git (Merge Conflicts):**
   - Terjadi ketika dua branch mengubah baris yang sama di file yang sama.
   - Diselesaikan secara sadar dengan membaca konteks kedua perubahan, menghapus penanda konflik (`<<<<<<<`, `=======`, `>>>>>>>`), memilih kode yang benar atau menggabungkan keduanya, lalu membuat commit penyelesaian.
4. **Isi Code Review yang Berkualitas:**
   - Objektif dan fokus pada kode (menyerang kode, bukan orangnya).
   - Menyebutkan lokasi baris kode secara spesifik.
   - Menjelaskan dampak teknis dan skenario risikonya (kenapa itu masalah).
   - Memberikan rekomendasi solusi konkret yang bisa langsung diterapkan.
