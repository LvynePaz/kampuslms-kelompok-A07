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

## 13.4 Checkpoint Minggu 13

1. **Berapa commit atas nama Anda?**
   Tercatat 140 commit (103 via `Traa897` dan 37 via `Patralysne`).
2. **Kenapa `git rm --cached .env` tidak cukup kalau `.env` sudah pernah ter-push?**
   Karena `git rm --cached` hanya menghapus berkas dari index commit berikutnya, tetapi riwayat berkas dan isi kredensialnya tetap terekam permanen di commit-commit sebelumnya dalam database Git object. Siapa pun bisa melihat commit lama lewat `git log` atau checkout commit lama. Rahasia yang terlanjur ter-push harus diganti nilainya.
3. **Tunjukkan satu temuan review yang Anda tulis. Jelaskan risikonya tanpa membaca catatan.**
   Temuan pada pengecekan otorisasi unduhan berkas privat: method download tanpa `Gate::authorize()` membuka celah IDOR di mana pengguna lain bisa mengunduh berkas tugas mahasiswa lain hanya dengan mengganti ID submission di URL browser.
4. **Tunjukkan satu temuan yang masuk ke kelompok Anda. Anda terima atau tolak? Kenapa?**
   Diterima. Temuan mengenai penanganan ukuran berkas null pada halaman submission (`b358b0f`). Perbaikan dilakukan dengan menambahkan pemeriksaan `optional($submission)->file_size` sebelum memformat ukuran file agar tidak menimbulkan exception saat mahasiswa belum mengunggah file.
5. **Kenapa PR sebaiknya kecil dan satu fitur?**
   PR yang kecil mudah dibaca, cepat direview, risiko konflik merge-nya rendah, dan jika terjadi bug mudah di-revert tanpa membatalkan fitur-fitur lainnya.
6. **Kenapa penilaian kontribusi tidak bisa hanya berdasarkan jumlah commit?**
   Karena pesan dan author commit di Git bisa diubah (`git commit --amend` atau `--author`), dan jumlah commit tidak mencerminkan kualitas maupun bobot kesulitan kode (misal 1 commit arsitektur besar bisa lebih bernilai dibanding 10 commit perbaikan typo).
