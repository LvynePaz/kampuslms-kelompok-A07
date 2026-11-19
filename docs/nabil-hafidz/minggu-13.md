*READ*

git log --oneline --graph --all
git shortlog -sn
git log --pretty='%an' | sort | uniq -c

1. Berapa commit atas nama Anda? Bandingkan dengan anggota lain.
Commit atas nama saya tercatat sebanyak **71 commit** (sekitar **23,9%** dari total 297 commit di repositori). Commit saya terbagi ke dalam 3 identitas/konfigurasi Git:
- `10241055-cpu`: **40 commit** (pengembangan fitur aplikasi, konfigurasi lingkungan, laporan mingguan, dan perbaikan kode lokal).
- `nabilh4fidz-png`: **20 commit** (fase awal proyek, penataan folder docs, inisialisasi catatan minggu 1–3).
- `Nabil Barok`: **11 commit** (aktivitas merge Pull Request melalui UI GitHub).

**Perbandingan dengan anggota lain:**
Saya berada pada posisi **kontributor terbesar kedua** di kelompok A07 setelah Patra Ananda (139 commit). Porsi commit Patra paling besar karena memimpin integrasi branch, CI/CD pipeline, dan setup live server Railway. Sementara itu, Nur Diah Indah mencatatkan 53 commit (fokus pada fitur tugas, submission, dan catatan mingguan) serta Nova Kartika mencatatkan 34 commit (fitur materi kuliah, seeder, dan laporan mingguan).

2. Adakah minggu di mana Anda tidak punya commit sama sekali?
**Tidak ada.** Aktivitas commit atas nama saya tercatat aktif secara konsisten setiap minggu kalender perkuliahan (dari Minggu ke-36 hingga Minggu ke-47 tahun 2026).

Berikut rincian commit saya berdasarkan minggu kalender:
- **Minggu ke-36 (31 Agu – 06 Sep 2026):** 8 commit *(Inisialisasi repo, catatan Minggu 1 & 2)*
- **Minggu ke-37 (07 Sep – 13 Sep 2026):** 7 commit *(Perbaikan rute, catatan Minggu 2 & 3)*
- **Minggu ke-38 (14 Sep – 20 Sep 2026):** 14 commit *(CRUD Pengguna, struktur data, merge branch)*
- **Minggu ke-39 (21 Sep – 27 Sep 2026):** 1 commit *(Catatan Minggu 4)*
- **Minggu ke-40 (28 Sep – 04 Okt 2026):** 4 commit *(Role-based routes & ownership check)*
- **Minggu ke-41 (05 Okt – 11 Okt 2026):** 9 commit *(Fitur dosen, materi, catatan Minggu 5 & 6)*
- **Minggu ke-42 (12 Okt – 18 Okt 2026):** 4 commit *(Read week 7, Build week 9 no 1 & 2)*
- **Minggu ke-43 (19 Okt – 25 Okt 2026):** 3 commit *(Read & Break week 9, update PR)*
- **Minggu ke-44 (26 Okt – 01 Nov 2026):** 10 commit *(Lonceng notifikasi navbar, cache dashboard, benchmark)*
- **Minggu ke-45 (02 Nov – 08 Nov 2026):** 3 commit *(Eksplorasi debugbar, merge PR #60)*
- **Minggu ke-46 (09 Nov – 15 Nov 2026):** 6 commit *(Setup database produksi, supervisor queue worker, build css)*
- **Minggu ke-47 (16 Nov – 22 Nov 2026):** 2 commit *(Merge PR #68, sinkronisasi main ke nabil_55)*

3. Berapa persen pesan commit yang mengikuti Conventional Commits?
Hanya sekitar **2,8%** (2 dari 71 commit) atau **3,8%** jika commit merge diabaikan (2 dari 52 commit non-merge).

Dua commit yang mengikuti spesifikasi resmi *Conventional Commits* (`type(scope): description`):
1. `2c0aa78f` — `feat(infra): konfigurasi database mysql kampuslms_prod, supervisor queue worker, dan laporan minggu 12 nabil`
2. `10051983` — `feat(perf): implementasi cache dashboard, dokumen benchmark m11, dan verifikasi env (build 1, 6, 7)`

**Evaluasi & Refleksi:**
Pada minggu-minggu awal hingga pertengahan (Minggu 1 s.d. 10), sebagian besar pesan commit saya masih berupa catatan informal atau imperatif bahasa Indonesia non-standar (contoh: `update catatan`, `public`, `debugbar`, `read break week 10`, `benerin minggu 3`, `update fitur dosen`). Format Conventional Commits baru mulai diimplementasikan secara sadar pada modul performa dan deployment (Minggu 11 dan 12).

4. Temukan satu commit Anda sendiri yang pesannya buruk. Tulis ulang seharusnya bagaimana.
- **Commit yang Ditemukan:**
  - **Hash:** `2072f8ad`
  - **Pesan Asli:** `bismillah`
  - **Tanggal:** Mon Aug 31 12:54:21 2026
  - **File yang Diubah:** `docs/minggu-01-nabil.md` (+1 baris)
- **Mengapa Pesan Ini Buruk?**
  Pesan `bismillah` sama sekali tidak memberikan informasi teknis tentang apa yang diubah, file apa yang ditambahkan, atau tujuan dari perubahan tersebut. Rekan satu kelompok atau reviewer tidak akan bisa memahami isi commit tanpa harus membuka diff kodenya.
- **Tulis Ulang Seharusnya (Sesuai Conventional Commits):**
  ```text
  docs(report): initialize weekly progress report for week 1
  ```

*(Contoh commit buruk lainnya sebagai bahan refleksi tim):*
- Commit `da311aca` — Pesan: `baikin route yang eor nabil`  
  Seharusnya: `fix(routes): resolve syntax error in web routes`
- Commit `b7089657` — Pesan: `public`  
  Seharusnya: `build(assets): update compiled css bundle in public directory`

5. `git log --stat` — Temukan commit terbesar Anda. Apakah seharusnya dipecah?
- **Hash Commit Terbesar:** `86108c7d` (di luar commit merge)
- **Pesan Commit:** `Update fitur dosen`
- **Tanggal:** Sun Oct 11 19:00:40 2026
- **Ukuran Perubahan:** **14 files changed, 1024 insertions(+), 64 deletions(-)** (Total 1.088 baris perubahan).
- **Daftar Berkas yang Terkena Dampak:**
  1. `. implode(` *(berkas aneh/sampah yang tidak sengaja terbuat akibat kesalahan pengetikan command)*
  2. `app/Http/Controllers/AssignmentController.php`
  3. `app/Http/Controllers/CourseController.php`
  4. `app/Http/Controllers/MaterialController.php`
  5. `resources/views/assignments/edit.blade.php`
  6. `resources/views/assignments/show.blade.php`
  7. `resources/views/courses/index.blade.php`
  8. `resources/views/courses/show.blade.php`
  9. `resources/views/dashboard.blade.php`
  10. `resources/views/materials/create.blade.php`
  11. `resources/views/materials/edit.blade.php`
  12. `resources/views/materials/show.blade.php`
  13. `routes/web.php`
  14. `tests/Feature/UtsDemoFlowTest.php`

- **Apakah Seharusnya Dipecah?**
  **YA, MUTLAK HARUS DIPECAH.**
  **Alasannya:**
  1. **Monolithic Changeset:** Commit ini mencampuradukkan 4 domain yang berbeda: modul materi (*materials*), modul tugas (*assignments*), tampilan mata kuliah & dashboard dosen (*courses/dashboard*), serta uji otomatis (*feature test UTS*).
  2. **Accidental Stray File:** Karena menggunakan `git add .` secara gegabah tanpa meninjau `git status`, sebuah file sampah bernama `. implode(` ikut ter-commit ke dalam repositori.
  3. **Sulit Direview & Rentan Konflik:** Diff sebesar 1.000+ baris menimbulkan *review fatigue* bagi anggota lain dan sangat rawan memicu konflik merge. Jika salah satu fitur materi mengalami bug, commit ini tidak bisa di-revert tanpa ikut membatalkan perbaikan tugas dan tes UTS.

- **Rekomendasi Pemecahan yang Seharusnya Dilakukan:**
  Commit tersebut idealnya dipecah menjadi minimal 4 commit atomik:
  1. `feat(materials): implement CRUD controllers and blade views for course materials`  
     *(Hanya `MaterialController.php` dan `views/materials/*`)*
  2. `feat(assignments): update assignment edit and show views for lecturers`  
     *(Hanya `AssignmentController.php` dan `views/assignments/*`)*
  3. `feat(courses): update lecturer dashboard layout and course route bindings`  
     *(Hanya `CourseController.php`, `views/courses/*`, `views/dashboard.blade.php`, dan `routes/web.php`)*
  4. `test(uts): add automated end-to-end feature test for uts demo workflow`  
     *(Hanya `tests/Feature/UtsDemoFlowTest.php`)*

*BREAK* 

| # | Percobaan Alur Kerja Git | Prediksi / Perilaku yang Diharapkan | Fakta Perilaku yang Terjadi | Penjelasan & Pembelajaran Penting |
|---|---|---|---|---|
| 1 | Menambahkan file `.env` berisi kredensial palsu ke repositori lalu commit & push | Pipeline CI/linter menolak atau push diblokir | Jika `.env` tidak ada di `.gitignore`, Git akan men-tracking file tersebut dan langsung terunggah ke repositori GitHub | `.gitignore` harus dicek sejak awal inisiasi proyek. Jangan pernah mengandalkan ketelitian manual untuk mencegah berkas sensitif terunggah. |
| 2 | Menjalankan `git rm --cached .env` setelah file terlanjur ter-push ke remote | File hilang dari repositori dan rahasia kembali aman | File memang hilang dari direktori kerja di commit terbaru (HEAD), tetapi seluruh riwayat commit lama masih menyimpan file `.env` beserta seluruh nilainya | `git rm --cached` **TIDAK MENGHAPUS** riwayat lama Git. Siapa pun dapat melakukan `git checkout <commit_lama>` atau melihat commit history di GitHub untuk membaca rahasia tersebut. Solusi wajib: segera rotasi (*ganti*) password yang bocor dan bersihkan riwayat menggunakan `git filter-repo` / BFG Repo-Cleaner. |
| 3 | Dua anggota mengedit baris yang sama pada file yang sama di dua branch berbeda lalu di-merge | Terjadi *merge conflict* dan Git meminta intervensi manual | Git menandai konflik menggunakan marker `<<<<<<<`, `=======`, dan `>>>>>>>`. Proses merge terhenti sampai konflik diselesaikan | Konflik Git tidak boleh diselesaikan dengan asal memilih salah satu sisi (*accept incoming/current* secara buta). Harus didiskusikan dengan rekan penulis kode agar logika kedua fitur tetap berjalan sinkron. |
| 4 | Push langsung ke branch `main` tanpa melalui Pull Request | Harusnya ditolak oleh remote GitHub | Push langsung berhasil masuk jika fitur *Branch Protection* di GitHub Settings belum diaktifkan | Tanpa aturan perlindungan branch (*Branch Protection Rules*), tim rentan melakukan bypass review secara tidak sengaja. Branch `main` wajib dikunci agar hanya menerima perubahan melalui PR yang sudah diapprove. |
| 5 | Menggabungkan (*merge*) Pull Request saat status automated test (CI) sedang merah/gagal | GitHub melarang tombol merge ditekan | Tombol "Merge pull request" tetap bisa diklik jika opsi *Require status checks to pass before merging* belum dicentang | Mengaktifkan GitHub Actions saja tidak cukup; branch protection harus mewajibkan CI berstatus hijau agar kode rusak tidak pernah masuk ke branch utama. |
| 6 | Memalsukan nama author commit dengan `git commit --author="Orang Lain <email@test.com>"` | Git memverifikasi kredensial asli pengguna | Commit tercatat atas nama orang lain di log Git lokal maupun kontribusi GitHub tanpa adanya peringatan | Git client secara default mempercayai string author lokal. Oleh karena itu, jumlah commit di `git shortlog` tidak bisa menjadi satu-satunya acuan penilaian kontribusi; wawancara teknis dan penguasaan kode tetap mutlak diperlukan. |

