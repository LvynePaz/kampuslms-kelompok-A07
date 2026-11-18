# Catatan Individu Minggu - 04 - Patra Ananda (10241061)


## 4. READ - BREAK - FIX - BUILD

4.1 READ — Telusuri Siklus Form Validasi Gagal

Tanpa AI. Sengaja membuat form tambah mata kuliah, mengisi SKS = 99, lalu mengirimnya untuk memahami alurnya dari awal ke akhir.

1. **Method yang menerima request:** `CourseController@store` — karena route `POST /courses` mengarah ke method `store` pada controller tersebut.

2. **Titik validasi terjadi:** Sebelum baris pertama method controller dieksekusi. Laravel melempar `ValidationException` lebih awal saat Form Request (atau `$request->validate()`) diproses di middleware pipeline. Controller bahkan tidak sempat berjalan jika validasi gagal.

3. **Tujuan redirect setelah gagal:** Laravel otomatis redirect kembali ke URL sebelumnya (`back()`). Ini sudah ditangani oleh framework — tidak perlu menulis kode redirect manual sama sekali.

4. **Dari mana `@error('sks')` mengambil pesan:** Dari session `errors` yang di-flash oleh Laravel saat redirect setelah validasi gagal. Blade membaca `$errors->first('sks')` di baliknya.

5. **Dari mana `old('sks')` mengambil nilai:** Dari flash session `_old_input`. Nilainya hanya hidup selama satu request berikutnya — setelah halaman dimuat, langsung hilang.

6. **Cookie session di DevTools:** Ditemukan cookie bernama `kampuslms_session` di Application → Cookies. Ini yang dipakai Laravel untuk melacak session antar request.

---

4.2 BREAK — Tujuh Kerusakan

Tulis prediksi lebih dulu, baru jalankan. Format ringkas seperti minggu sebelumnya.

---

##### BREAK 1: Hapus `@csrf` dari Form → Error 419

* **Yang Dirusak:** Direktif `@csrf` di dalam form tambah mata kuliah dihapus.
* **Cara Coba:** Kirim form biasa lewat browser.
* **Yang Terjadi:** Browser mendapat respon **`419 Page Expired`**. Laravel menolak request karena tidak ada token CSRF yang cocok.
* **Kenapa Bahaya:** Tanpa CSRF, situs jahat bisa memasang form tersembunyi yang mengirim POST ke aplikasi memakai session korban yang sedang login — korban tidak tahu sama sekali.
* **Solusi Benar:** Selalu pasang `@csrf` di dalam setiap form yang menggunakan method POST, PUT, PATCH, atau DELETE.

---

##### BREAK 2: Ganti `$request->validated()` Menjadi `$request->all()` + Uji Lewat cURL

* **Yang Dirusak:** Di `CourseController::store()`, diubah dari `Course::create($request->validated())` menjadi `Course::create($request->all())`.
* **Cara Coba Singkat:**
  ```bash
  curl -X POST http://127.0.0.1:8000/courses \
    -H "Accept: application/json" \
    -d "code=SI99" -d "name=Audit Keamanan" -d "sks=3" \
    -d "lecturer_id=1" -d "status=active" \
    -d "is_admin=1" -d "created_at=2020-01-01"
  ```
* **Yang Terjadi:** Field `is_admin` dan `created_at` yang tidak ada di form masuk ke payload dan bisa tersimpan jika terdaftar di `$fillable`.
* **Kenapa Bahaya:** `$request->all()` menyerahkan seluruh payload mentah dari client. `$request->validated()` adalah whitelist yang hanya mengembalikan data yang lolos `rules()` — field liar dibuang otomatis.
* **Solusi Benar:** Selalu gunakan `$request->validated()` saat menyimpan data ke Eloquent.

---

##### BREAK 3: Hapus Validasi `exists:users,id` pada `lecturer_id`

* **Yang Dirusak:** Aturan `'exists:users,id'` dihapus dari `StoreCourseRequest`.
* **Cara Coba Singkat:**
  ```bash
  curl -X POST http://127.0.0.1:8000/courses \
    -H "Accept: application/json" \
    -d "code=SI98" -d "name=Basis Data Lanjut" -d "sks=3" \
    -d "lecturer_id=99999" -d "status=active"
  ```
* **Yang Terjadi:** Jika ada foreign key constraint di database → error 500 (`SQLSTATE[23000]: Integrity constraint violation`). Jika tidak ada → data yatim masuk tanpa relasi valid.
* **Kenapa Bahaya:** Input dari client tidak bisa diasumsikan valid. Tanpa `exists`, integritas relasional bergantung pada keberuntungan.
* **Solusi Benar:** Pasang `'lecturer_id' => ['required', 'exists:users,id']` agar ID fiktif ditolak dengan pesan `422 Unprocessable Content` yang ramah.

---

##### BREAK 4: Hapus Validasi `in:...` pada Field `status`

* **Yang Dirusak:** Aturan `'in:draft,active,archived'` dihapus.
* **Cara Coba Singkat:**
  ```bash
  curl -X POST http://127.0.0.1:8000/courses \
    -H "Accept: application/json" \
    -d "code=SI97" -d "name=Kriptografi" -d "sks=3" \
    -d "lecturer_id=1" -d "status=superadmin"
  ```
* **Yang Terjadi:** String `"superadmin"` masuk dan tersimpan di kolom status.
* **Kenapa Bahaya:** Logika filter (`where('status', 'active')`) dan badge tampilan Blade jadi rusak atau tidak konsisten karena nilai di luar domain sistem.
* **Solusi Benar:** Kunci nilai yang diizinkan dengan `'status' => ['required', 'in:draft,active,archived']`.

---

##### BREAK 5: Hapus `->withQueryString()` pada Pagination

* **Yang Dirusak:** Baris `->withQueryString()` dihapus dari query index.
* **Cara Coba:**
  1. Buka `/courses?q=basis&status=active`
  2. Klik halaman 2 di pagination
* **Yang Terjadi:** URL berubah menjadi `/courses?page=2`, kata kunci `q=basis` dan `status=active` hilang. Halaman 2 menampilkan semua data tanpa filter.
* **Kenapa Bahaya:** Bug UX klasik. Pengguna bingung karena pencarian yang aktif tiba-tiba ter-reset saat pindah halaman.
* **Solusi Benar:** Selalu rantaikan `->withQueryString()` setelah `->paginate()`.

---

##### BREAK 6: Ganti `return redirect()` Menjadi `return view()` pada `store`

* **Yang Dirusak:** Controller mengembalikan view langsung setelah menyimpan data.
* **Cara Coba:**
  1. Isi form create mata kuliah, tekan Simpan
  2. Begitu halaman detail tampil, tekan F5
* **Yang Terjadi:** Browser memunculkan dialog *"Confirm Form Resubmission"*. Jika dilanjutkan, data mata kuliah tersimpan dua kali.
* **Kenapa Bahaya:** Melanggar pola PRG (Post-Redirect-Get). Data akademik bisa terduplikasi hanya karena pengguna me-refresh.
* **Solusi Benar:** Setelah POST/PUT/DELETE berhasil, selalu akhiri dengan `return redirect()->route(...)`.

---

##### BREAK 7: Hapus `old(...)` dari Semua Input

* **Yang Dirusak:** Semua atribut `value="{{ old('field') }}"` dihapus dari input form.
* **Cara Coba:**
  1. Isi form dengan 6 field lengkap
  2. Sengaja isi SKS = 99
  3. Tekan Simpan
* **Yang Terjadi:** Validasi gagal, error tampil, tapi seluruh field yang tadi diisi jadi kosong kembali.
* **Kenapa Bahaya:** Pengguna harus mengetik ulang semuanya dari nol hanya karena satu kesalahan kecil.
* **Solusi Benar:** Pasang `value="{{ old('nama_field', $model->nama_field ?? '') }}"` di setiap input.

---

4.3 FIX — Perbaikan Branch W04

Pada repositori `LMS-Broken` branch `w04` ditemukan 6 masalah:

1. **Validasi hanya di frontend** — controller langsung `create($request->all())` tanpa Form Request. Penyerang bisa bypass total lewat cURL.
2. **`unique` pada update menolak dirinya sendiri** — aturan ditulis polos `unique:courses,code` tanpa `ignore()`. Edit tanpa ubah kode selalu gagal.
3. **Filter disimpan di session** — bukan query string. Dua tab browser saling menimpa, URL tidak bisa dibagikan.
4. **Pagination kehilangan query string** — tidak ada `->withQueryString()`. Filter hilang saat pindah halaman.
5. **`store` tanpa redirect** — langsung `return view()` setelah simpan. Refresh halaman = data duplikat.
6. **Form tanpa `@csrf`** — form create terbuka untuk serangan CSRF.

---

4.4 BUILD — Implementasi di Proyek

Yang dibangun di `kampuslms-kelompok-A07`:

1. **Form Request terpisah:** `StoreCourseRequest`, `UpdateCourseRequest`, `StoreUserRequest`, `UpdateUserRequest` — masing-masing dengan `messages()` bahasa Indonesia dan `authorize(): true` + komentar TODO Policy minggu 7.

2. **Update yang benar:** `UpdateCourseRequest` dan `UpdateUserRequest` memakai `Rule::unique()->ignore($this->route('course'))` agar edit tanpa ubah kode tidak gagal.

3. **Controller upgrade:** `store()` dan `update()` kini menerima Form Request. `index()` kedua controller mendukung pencarian dan filter via query string dengan `->when()` + `->withQueryString()`.

4. **Flash message global** diletakkan di `layout.blade.php` — berlaku otomatis untuk seluruh halaman tanpa perlu ditulis ulang per-view.

5. **Filter courses:** pencarian kode/nama + dropdown status. **Filter users:** pencarian nama/email/NIM-NIP + dropdown role. Keduanya memakai form `GET`, bukan session.

6. **Field status** ditambahkan ke form `create` dan `edit` mata kuliah — sebelumnya tidak ada sehingga status selalu default `active`.

---

4.5 CHECKPOINT MINGGU 4 (VERSI LIVE WEBSITE MINGGU 12)

Kalimat Pembuka ke Dosen:
"Izin menjelaskan, Pak. Seluruh konsep pengelolaan state, validasi, dan penanganan data dari Minggu 4 tetap kami pertahankan secara utuh sebagai fondasi arsitektur di proyek KampusLMS kami. Perbedaannya, karena sekarang proyek sudah berada di Minggu 12 (fase produksi dan sudah menerapkan RBAC dari Minggu 7), seluruh formulir dan rute manajemen mata kuliah kini berada di bawah rute panel Admin (/admin/courses) serta diamankan oleh CoursePolicy. Oleh karena itu, untuk memperagakannya di website live, saya login menggunakan akun Admin: admin@kampuslms.test."

Kredensial Pengujian Live:
- URL: `https://kampuslms-kelompok-a07-production.up.railway.app/login`
- Akun: `admin@kampuslms.test` / `password`

1. **Kenapa validasi di JavaScript tidak dianggap keamanan? Peragakan cara melewatinya.**
   - **Cara Peragakan di Website Live:**
     1. Login sebagai Admin di `/login`.
     2. Buka menu Mata Kuliah atau langsung ke `/admin/courses/create`.
     3. Buka DevTools (F12) -> tab Elements -> cari `<input type="number" id="sks" name="sks" min="1" max="6">`.
     4. Ubah atribut `max="6"` menjadi `max="999"`, lalu isi form dengan SKS = 99 dan submit.
     5. Hasil di layar: Form kembali tertahan dengan pesan error merah *"SKS harus antara 1 sampai 6."* Ini membuktikan validasi HTML/JS berhasil diakali di browser, tapi server-side Form Request Laravel tetap menghadang.
   - **Jawaban ke Dosen:** Validasi JavaScript/HTML berjalan di browser (client-side) yang berada di bawah kendali penuh pengguna. Penyerang dapat mematikan JS, memanipulasi DOM di DevTools, atau menembak request HTTP mentah untuk membypass validasi frontend. Validasi frontend hanya untuk kenyamanan pengguna (UX), sedangkan keamanan nyata mutlak berada di server-side (Form Request Laravel).

2. **Apa yang dikembalikan `$request->validated()` dan kenapa lebih aman daripada `$request->all()`?**
   - **Cara Peragakan:** Tunjukkan file `CourseController@store` baris 60 dan `StoreCourseRequest.php`.
   - **Jawaban ke Dosen:** `$request->validated()` hanya mengembalikan field yang terdaftar dan lolos aturan di `rules()` Form Request (whitelist). Sebaliknya, `$request->all()` mengembalikan semua data mentah yang dikirim client. Jika ada penyerang menyelipkan input liar seperti `is_admin=1` atau `role=admin`, `$request->all()` akan meneruskannya ke Eloquent dan membuka celah kerentanan Mass Assignment jika fillable bocor. `$request->validated()` otomatis membuang field liar tersebut.

3. **Jelaskan pola PRG. Apa yang terjadi kalau `store` mengembalikan view?**
   - **Cara Peragakan:** Tunjukkan baris `return redirect()->route('admin.courses.index')->with('success', ...)` di `CourseController.php` baris 81-83. Simpan data di website live, lihat URL otomatis berpindah ke GET `/admin/courses` dan tekan F5 (halaman aman di-refresh tanpa dialog Confirm Resubmission).
   - **Jawaban ke Dosen:** PRG (Post-Redirect-Get) adalah pola di mana setelah request POST berhasil diproses dan disimpan di server, server merespons dengan HTTP Redirect (302) ke route tujuan, dan browser otomatis melakukan request GET ke route tersebut. Jika `store` langsung `return view()`, status terakhir di browser tetap POST. Ketika pengguna me-refresh halaman (F5) atau menekan tombol Back, browser akan mengulang request POST tersebut sehingga data akademik terduplikasi (double submit).

4. **Kenapa filter pencarian sebaiknya di query string, bukan session? Beri satu skenario yang rusak kalau dipindah ke session.**
   - **Cara Peragakan:** Di web live `/admin/courses`, cari kata kunci dan filter status. Perhatikan URL berubah menjadi `/admin/courses?q=Web&status=active`. Buka link tersebut di tab baru, filternya langsung tampil persis.
   - **Jawaban ke Dosen:** Filter pencarian adalah state navigasi yang melekat pada URL. Skenario yang rusak jika disimpan di session:
     - **Masalah Multi-Tab:** Pengguna membuka Tab 1 mencari "Pemrograman Web" dan Tab 2 mencari "Basis Data". Session server akan tertimpa oleh Tab 2. Saat pengguna kembali ke Tab 1 dan berpindah halaman, daftar mata kuliah tiba-tiba berubah menjadi "Basis Data".
     - **URL Tidak Bisa Dibagikan:** Dosen/mahasiswa tidak bisa menyalin link URL hasil filter untuk dibagikan ke orang lain karena parameternya tersimpan di session lokal browser, bukan di alamat URL.
     - **Tombol Back Rusak:** Riwayat navigasi browser menjadi tidak konsisten.

5. **Apa fungsi `@csrf`? Serangan apa yang dicegahnya, dan bagaimana serangan itu bekerja?**
   - **Cara Peragakan:** Tunjukkan `<input type="hidden" name="_token" value="...">` di form `/admin/courses/create` via Inspect Element. Jika token dihapus, Laravel menolak dengan error `419 Page Expired`.
   - **Jawaban ke Dosen:** `@csrf` menghasilkan token acak rahasia yang disimpan di sesi pengguna dan disisipkan sebagai input tersembunyi (`_token`). Serangan yang dicegah adalah CSRF (Cross-Site Request Forgery). Cara kerja serangan: Korban sedang login di aplikasi KampusLMS, lalu membuka situs berbahaya milik penyerang di tab lain. Situs tersebut diam-diam mengirim form POST ke KampusLMS. Karena korban sedang login, browser otomatis melampirkan cookie sesi korban. Tanpa `@csrf`, server mengira request itu sah dari korban. Dengan `@csrf`, server menolak request karena situs penyerang tidak bisa membaca token rahasia dari sesi korban.

6. **Kenapa aturan `unique` pada update perlu `ignore()`?**
   - **Cara Peragakan:** Buka `/admin/courses/1/edit` di website live, ubah nama mata kuliah tanpa mengubah kodenya, klik Simpan. Update berhasil tanpa error duplikasi kode. Tunjukkan kodenya di `UpdateCourseRequest.php`: `Rule::unique('courses', 'code')->ignore($this->route('course'))`.
   - **Jawaban ke Dosen:** Pada saat update, record tersebut sudah tersimpan di database. Jika pengguna hanya ingin mengubah nama mata kuliah atau SKS tanpa mengubah kodenya, aturan `unique:courses,code` polos akan mendeteksi kode itu sudah ada (yaitu milik record itu sendiri) dan menolak penyimpanan dengan error duplikasi. Dengan `->ignore($id)`, Laravel mengecualikan ID record yang sedang diedit dari pengecekan keunikan database (`WHERE id != ?`), sehingga proses edit tetap berhasil.

