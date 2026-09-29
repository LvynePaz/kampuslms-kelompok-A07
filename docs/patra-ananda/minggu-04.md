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
