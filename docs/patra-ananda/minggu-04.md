# Catatan Individu Minggu 04 - Patra Ananda (10241061)

## 1. Progres Pengerjaan
- Mempelajari siklus hidup pengelolaan *state* pada arsitektur web stateless (Query String, Session, Flash Session, Cookie, dan Database).
- Mengimplementasikan pola **Post-Redirect-Get (PRG)** pada operasi penyimpanan dan pembaruan data untuk mencegah duplikasi request.
- Membangun sistem validasi di sisi server menggunakan **Form Request** (`StoreCourseRequest` dan `UpdateCourseRequest`) di Laravel 12.
- Mengamankan formulir dari serangan CSRF dengan `@csrf` dan mempertahankan input lama saat validasi gagal menggunakan helper `old()` dan `@error`.
- Mengimplementasikan fitur pencarian, filter status, dan pagination yang mempertahankan parameter URL menggunakan `->withQueryString()`.
- Menyelesaikan perbaikan 6 bug pada repositori latihan `LMS-Broken` branch `w04`.

---

## 2. Hasil Eksplorasi (Read - Break - Fix - Build)

### 2.1 READ — Penelusuran Siklus Form Validasi Gagal (30 Menit)

Pengujian dilakukan dengan membuat form tambah mata kuliah, mengisinya dengan data yang sengaja tidak valid (`sks = 99`), lalu menelusuri siklus request-nya:

1. **Method apa yang menerima request? Di controller mana?**
   Request HTTP `POST /courses` diterima oleh method `store(StoreCourseRequest $request)` di dalam controller `app/Http/Controllers/CourseController.php`.

2. **Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?**
   Validasi terjadi **sebelum** baris pertama di dalam kurung kurawal `{ ... }` method controller dieksekusi. 
   *Mekanisme:* Laravel menggunakan teknik *Method Injection* dan *Form Request Lifecycle*. Sebelum method `store()` dipanggil, Laravel IoC Container me-resolve `StoreCourseRequest`, mengecek method `authorize()`, dan langsung mengeksekusi method `rules()`. Jika aturan validasi gagal, Laravel langsung melempar `ValidationException` dan memicu proses redirect kembali. Baris kode di controller sama sekali tidak tersentuh.

3. **Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?**
   Laravel secara otomatis me-redirect pengguna kembali ke halaman form sebelumnya (`/courses/create`).
   *Yang menentukan:* Ditentukan oleh kelas bawaan `Illuminate\Foundation\Http\FormRequest` melalui method `getRedirectUrl()`, yang secara default mengambil URL asal dari HTTP Header `Referer` atau session `url.intended` (setara dengan memanggil helper `back()`).

4. **Dari mana `@error('sks')` mengambil pesannya?**
   Diambil dari flash session data error bag `$errors` (berupa objek `Illuminate\Support\ViewErrorBag`) yang secara otomatis diinjeksi ke semua view Blade oleh middleware global `ShareErrorsFromSession`. Direktif `@error('sks')` melakukan pengecekan `$errors->has('sks')` dan mengekstrak pesan pertama `$errors->first('sks')` ke dalam variabel `$message`.

5. **Dari mana `old('sks')` mengambil nilainya? Berapa lama nilai itu bertahan?**
   Helper `old('sks')` mengambil nilai input dari **Flash Session** (disimpan via `$request->flash()` saat validasi gagal).
   *Umur data:* Nilai ini bersifat sementara (*flashed*) dan **hanya bertahan selama tepat satu request berikutnya**. Jika halaman di-refresh (F5) atau pengguna berpindah halaman lain, data `old()` akan otomatis dihapus oleh middleware session.

6. **Cookie session Laravel di DevTools:**
   Pada browser (Inspect Element → Application → Cookies → `http://127.0.0.1:8000`), ditemukan cookie session dengan nama:
   **`laravel_session`** (atau `kampuslms_session` jika nama aplikasi di `.env` telah disesuaikan). Cookie ini berisi token unik terenkripsi yang digunakan server untuk mencocokkan request pengguna dengan file session di `storage/framework/sessions/`.

---

### 2.2 BREAK — Eksplorasi 7 Kerusakan State & Validasi (Hands-on)

Berikut adalah rangkuman 7 eksperimen pengujian kerusakan yang dicoba secara langsung melalui terminal, cURL, dan browser:

---

##### BREAK 1: Hapus `@csrf` dari Form Tambah Mata Kuliah
* **Yang Dirusak:** Menghapus direktif `@csrf` dari dalam tag `<form method="POST">` pada `resources/views/courses/create.blade.php`.
* **Cara Coba Singkat:**
  1. Buka file form create, hapus baris `@csrf`.
  2. Buka browser, isi form mata kuliah, lalu klik tombol **Simpan**.
* **Yang Terjadi di Browser:** Muncul layar error **`419 Page Expired`** (`TokenMismatchException`).
* **Kenapa Bahaya & Apa yang Dicegah:** `@csrf` menghasilkan token unik tersembunyi yang memastikan request POST benar-benar berasal dari pengguna di situs kita, bukan dikirim oleh situs penyerang dari tab lain menggunakan session/cookie korban yang sedang aktif (*Cross-Site Request Forgery*). Tanpa token ini, siapa pun bisa membuat situs jebakan yang mengirim POST untuk menghapus atau menambah data di LMS atas nama akun korban.
* **Solusi Benar:** Selalu sertakan `@csrf` di setiap form HTML yang menggunakan method `POST`, `PUT`, `PATCH`, atau `DELETE`.

---

##### BREAK 2: Ganti `$request->validated()` Menjadi `$request->all()` + Uji Lewat cURL
* **Yang Dirusak:** Di `CourseController::store()`, pemanggilan data diubah dari `Course::create($request->validated())` menjadi `Course::create($request->all())`.
* **Cara Coba Singkat:**
  Kirim request POST via terminal menggunakan `curl` dengan menyisipkan field siluman (`is_admin=1` dan `created_at=2020-01-01`):
  ```bash
  curl -X POST http://127.0.0.1:8000/courses \
    -H "Accept: application/json" \
    -d "code=SI99" -d "name=Audit Keamanan" -d "sks=3" \
    -d "lecturer_id=1" -d "status=active" \
    -d "is_admin=1" -d "created_at=2020-01-01"
  ```
* **Yang Terjadi di Database:** Jika kolom yang diselipkan terdaftar di `$fillable` (atau menggunakan `$guarded = []`), input siluman yang tidak pernah ada di tampilan form HTML berhasil masuk dan disimpan ke database (*Mass Assignment Vulnerability*).
* **Kenapa Bahaya:** `$request->all()` menyerahkan seluruh payload mentah yang dikirim oleh client. Penyerang dapat menyuntikkan data sensitif (misal mengubah role, status kelulusan, atau tanggal audit). Sebaliknya, `$request->validated()` adalah *whitelist* yang hanya mengembalikan data yang sah dan lolos aturan `rules()`.
* **Solusi Benar:** Jangan pernah memakai `$request->all()` saat menyimpan data ke model Eloquent. Selalu gunakan `$request->validated()`.

---

##### BREAK 3: Hapus Validasi `exists:users,id` pada `lecturer_id` (Kirim ID Fiktif)
* **Yang Dirusak:** Menghapus aturan `'exists:users,id'` pada field `lecturer_id` di `StoreCourseRequest`, lalu mengirim ID dosen yang tidak terdaftar di database.
* **Cara Coba Singkat:**
  Jalankan perintah cURL berikut:
  ```bash
  curl -X POST http://127.0.0.1:8000/courses \
    -H "Accept: application/json" \
    -d "code=SI98" -d "name=Basis Data Lanjut" -d "sks=3" \
    -d "lecturer_id=99999" -d "status=active"
  ```
* **Yang Terjadi:** Jika tabel database memiliki *foreign key constraint*, database memuntahkan error fatal **`SQLSTATE[23000]: Integrity constraint violation (Error 500)`** yang membuat server crash dan menampilkan detail internal aplikasi ke pengguna. Jika database tidak diproteksi FK, masuklah **data yatim (*orphan record*)** yang merusak relasi model.
* **Kenapa Bahaya:** Input dari client tidak boleh diasumsikan valid. Tanpa aturan `exists`, integritas relasional database bergantung pada keberuntungan.
* **Solusi Benar:** Pasang aturan `'lecturer_id' => ['required', 'exists:users,id']` agar jika ID dosen fiktif dikirim, sistem mengembalikan pesan validasi ramah `422 Unprocessable Content` tanpa membuat database error.

---

##### BREAK 4: Hapus Validasi `in:...` pada Field `status` (Kirim Status Liar)
* **Yang Dirusak:** Menghapus validasi enum `'in:draft,active,archived'` pada field `status`.
* **Cara Coba Singkat:**
  Kirim request dengan status yang tidak ada dalam spesifikasi:
  ```bash
  curl -X POST http://127.0.0.1:8000/courses \
    -H "Accept: application/json" \
    -d "code=SI97" -d "name=Kriptografi" -d "sks=3" \
    -d "lecturer_id=1" -d "status=superadmin"
  ```
* **Yang Terjadi:** Nilai string `"superadmin"` tersimpan di kolom status mata kuliah.
* **Kenapa Bahaya:** Logika filter di query (`where('status', 'active')`) dan tampilan badge status di Blade akan menjadi rusak atau tidak konsisten karena nilai yang masuk berada di luar domain sistem.
* **Solusi Benar:** Kunci daftar nilai yang diizinkan menggunakan aturan `'status' => ['required', 'in:draft,active,archived']`.

---

##### BREAK 5: Hapus `->withQueryString()` pada Pagination Pencarian
* **Yang Dirusak:** Di controller index, baris `->withQueryString()` pada pemanggilan pagination dikomentari/dihapus:
  ```php
  $courses = Course::latest()->paginate(15); // tanpa ->withQueryString()
  ```
* **Cara Coba Singkat:**
  1. Buka browser ke `/courses?q=basis&status=active`.
  2. Hasil pencarian menampilkan data yang difilter.
  3. Klik tombol **Next** atau tombol angka **2** pada komponen pagination di bawah.
* **Yang Terjadi di Browser:** URL berubah menjadi `/courses?page=2`, kata kunci `q=basis` dan `status=active` hilang dari URL! Halaman 2 menampilkan seluruh mata kuliah secara umum, bukan kelanjutan dari hasil pencarian.
* **Kenapa Bahaya:** Ini adalah bug UX klasik. Pengguna merasa bingung karena pencarian yang sedang mereka lakukan tiba-tiba ter-reset saat berpindah halaman.
* **Solusi Benar:** Selalu panggil `->withQueryString()` sebelum atau sesudah `->paginate(15)`.

---

##### BREAK 6: Ganti `return redirect()` Menjadi `return view()` pada Method `store`
* **Yang Dirusak:** Mengembalikan view secara langsung setelah menyimpan data tanpa melakukan redirect:
  ```php
  // Salah:
  Course::create($request->validated());
  return view('courses.show', compact('course'));
  ```
* **Cara Coba Singkat:**
  1. Isi form create mata kuliah, tekan **Simpan**.
  2. Begitu halaman detail tampil, tekan tombol refresh browser (**F5**).
* **Yang Terjadi di Browser:** Browser memunculkan dialog pop-up: *"Confirm Form Resubmission. The page that you're looking for used information that you entered..."*. Jika pengguna menekan tombol **Continue / Lanjutkan**, data mata kuliah yang sama akan tersimpan untuk kedua kalinya (**data ganda**).
* **Kenapa Bahaya:** Melanggar standar arsitektur web **PRG (Post-Redirect-Get)**. Jika pengguna me-refresh halaman atau koneksi lambat, data transaksi/akademik akan terduplikasi berkali-kali.
* **Solusi Benar:** Setelah memproses request HTTP POST/PUT/DELETE, selalu akhiri dengan `return redirect()->route(...)`.

---

##### BREAK 7: Hapus Helper `old(...)` dari Input Form
* **Yang Dirusak:** Mengubah tag input form dari `<input value="{{ old('name') }}">` menjadi `<input value="">`.
* **Cara Coba Singkat:**
  1. Isi formulir dengan 6 field data secara lengkap dan panjang.
  2. Sengaja salahkan satu field saja (misal isi SKS = 99).
  3. Tekan **Simpan**.
* **Yang Terjadi di Browser:** Validasi gagal, error ditampilkan, tetapi seluruh kotak input yang tadi sudah diketik menjadi kosong kembali.
* **Kenapa Bahaya:** Pengguna harus mengetik ulang seluruh isian dari nol hanya karena satu kesalahan kecil. Ini merupakan keluhan utama pada aplikasi formulir kampus yang buruk.
* **Solusi Benar:** Pasang atribut `value="{{ old('nama_field', $model->nama_field ?? '') }}"` pada setiap elemen input form.

---

### 2.3 FIX — Laporan Perbaikan Branch W04 (`LMS-Broken`)

Pada repositori `LMS-Broken` branch `w04`, ditemukan dan diperbaiki **6 masalah kritis**:

#### 1. Validasi Hanya Dilakukan di Sisi Frontend
- **Berkas:** `resources/views/courses/create.blade.php` & `app/Http/Controllers/CourseController.php`
- **Penyebab:** Controller langsung menjalankan `Course::create($request->all())` tanpa Form Request, mengandalkan atribut HTML5 `required` di browser semata.
- **Dampak bagi Pengguna:** Jika ada gangguan jaringan atau browser lama, tidak ada feedback error terstruktur.
- **Dampak bagi Penyerang:** Sangat fatal. Penyerang cukup menggunakan tool seperti `curl` atau Postman untuk melewati form browser dan mengirim data kosong, nilai negatif, atau script berbahaya langsung ke database.
- **Perbaikan:** Membuat `StoreCourseRequest`, memvalidasi seluruh tipe data, panjang string, dan foreign key di server, serta memanggil `$request->validated()` di controller.

#### 2. Aturan `unique` pada Update Menolak Dirinya Sendiri
- **Berkas:** `app/Http/Requests/UpdateCourseRequest.php`
- **Penyebab:** Aturan ditulis polos `'code' => 'required|unique:courses,code'`.
- **Dampak bagi Pengguna:** Dosen yang hanya ingin mengganti deskripsi atau SKS mata kuliah tanpa mengubah kodenya akan selalu gagal menyimpan, karena Laravel menganggap kode tersebut sudah terpakai di database (oleh baris datanya sendiri).
- **Dampak bagi Penyerang:** Mengakibatkan bug fungsional (*denial of service* terhadap fitur edit).
- **Perbaikan:** Menambahkan pengecualian ID saat update menggunakan `Rule::unique('courses', 'code')->ignore($this->route('course'))`.

#### 3. State Filter Pencarian Disimpan di dalam Session Server
- **Berkas:** `app/Http/Controllers/CourseController.php`
- **Penyebab:** Controller menyimpan parameter pencarian ke session: `session(['q' => $request->q])`.
- **Dampak bagi Pengguna:** Merusak alur navigasi. Jika pengguna membuka dua tab browser (misal mencari "Basis Data" di Tab 1 dan "Jaringan" di Tab 2), tab satu akan menimpa filter tab lain. Tautan hasil pencarian juga tidak bisa dibagikan (*unshareable URL*) dan tombol *Back* browser menjadi kacau.
- **Dampak bagi Penyerang:** Memboroskan memori session server (*Session Flooding/Bloat*).
- **Perbaikan:** Mengubah state filter agar sepenuhnya membaca dari Query String URL (`$request->filled('q')`), bukan dari session.

#### 4. Pagination Kehilangan Parameter Query String
- **Berkas:** `app/Http/Controllers/CourseController.php`
- **Penyebab:** Pemanggilan paginasi tidak merantai method query string: `Course::paginate(15)`.
- **Dampak bagi Pengguna:** Ketika pengguna mencari kata kunci dan berpindah ke halaman 2, hasil pencarian hilang dan daftar kembali menampilkan semua data.
- **Perbaikan:** Menambahkan `->withQueryString()` pada query paginasi controller.

#### 5. Method `store` Tidak Menerapkan Pola PRG (Post-Redirect-Get)
- **Berkas:** `app/Http/Controllers/CourseController.php`
- **Penyebab:** Controller mengembalikan view secara langsung setelah menyimpan data (`return view('courses.show', ...)`).
- **Dampak bagi Pengguna:** Terjadi duplikasi data mata kuliah secara tidak sengaja ketika pengguna me-refresh halaman (F5).
- **Dampak bagi Penyerang:** Mempermudah eksploitasi pengiriman data ganda secara berulang.
- **Perbaikan:** Mengubah nilai return menjadi `return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil ditambahkan.');`.

#### 6. Form Tambah Data Tidak Dilindungi Token CSRF
- **Berkas:** `resources/views/courses/create.blade.php`
- **Penyebab:** Tidak ada direktif `@csrf` di dalam tag formulir HTML.
- **Dampak bagi Pengguna:** Sering mengalami error tidak jelas atau rentan akunnya disalahgunakan.
- **Dampak bagi Penyerang:** Penyerang dapat membuat halaman jebakan di luar LMS yang mengeksekusi request POST pemalsuan ke aplikasi ini menggunakan hak akses session pengguna yang sedang login.
- **Perbaikan:** Menambahkan direktif `@csrf` tepat di bawah tag pembuka `<form>`.

---

### 2.4 BUILD — Form dan Daftar yang Layak Pakai

Berikut adalah implementasi nyata yang telah dibangun di proyek kelompok `kampuslms-kelompok-A07`:

1. **Membuat Form Request Khusus:**
   - Dibuat `app/Http/Requests/StoreCourseRequest.php` dan `app/Http/Requests/UpdateCourseRequest.php`.
   - Mengonfigurasi pesan validasi ramah berbahasa Indonesia melalui method `messages()`.
   - Menangani aturan `unique` update secara tepat via `Rule::unique('courses', 'code')->ignore($this->route('course'))`.

2. **Form Blade yang Tahan Kesalahan:**
   - Form `courses/create.blade.php` dan `courses/edit.blade.php` dilengkapi dengan atribut `value="{{ old('field', $course->field ?? '') }}"`.
   - Menampilkan indikator pesan error di bawah masing-masing input menggunakan `@error('field') <p class="text-sm text-red-500">{{ $message }}</p> @enderror`.
   - Memasang proteksi `@csrf` serta method spoofing `@method('PUT')` pada form edit.

3. **Pesan Notifikasi Global (Flash Message):**
   - Menambahkan komponen flash message di dalam layout bersama `resources/views/components/layout.blade.php`:
     ```blade
     @if (session('success'))
         <div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg">
             {{ session('success') }}
         </div>
     @endif
     ```

4. **Daftar Mata Kuliah Lengkap dengan Filter & Paginasi:**
   - Menggunakan query dinamis dengan eager loading untuk menghindari masalah N+1:
     ```php
     $courses = Course::query()
         ->with('lecturer')
         ->when($request->filled('q'), function ($query) use ($request) {
             $query->where(function ($sub) use ($request) {
                 $sub->where('name', 'like', '%' . $request->q . '%')
                     ->orWhere('code', 'like', '%' . $request->q . '%');
             });
         })
         ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
         ->latest()
         ->paginate(15)
         ->withQueryString();
     ```

5. **Penghapusan Aman dengan Konfirmasi:**
   - Tombol hapus dibungkus dalam form dengan method spoofing `@method('DELETE')` dan `@csrf`.
   - Menambahkan konfirmasi JavaScript `onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?');"` sebelum request dikirimkan ke server.

---

## 3. CHECKPOINT — Evaluasi Mandiri & Persiapan Wawancara

- [x] **Kenapa validasi di JavaScript tidak dianggap keamanan? Peragakan cara melewatinya.**
  *Jawaban:* Validasi JavaScript (dan atribut HTML seperti `required`) berjalan di browser pengguna (sisi client), yang sepenuhnya berada di bawah kendali pengguna. Validasi ini bertujuan untuk kenyamanan pengguna (*User Experience*) agar tidak perlu menunggu respon server untuk kesalahan sepele. Cara melewatinya sangat mudah: cukup matikan JavaScript di browser, ubah DOM form via Inspect Element, atau langsung kirim request HTTP POST menggunakan tools seperti cURL atau Postman. Pertahanan keamanan sejati wajib berada di sisi server.

- [x] **Apa yang dikembalikan `$request->validated()` dan kenapa lebih aman daripada `$request->all()`?**
  *Jawaban:* `$request->validated()` mengembalikan *array* yang **hanya berisi field yang didefinisikan dan lolos aturan validasi** pada Form Request. Field tambahan apa pun yang diselipkan oleh penyerang akan otomatis dibuang. Sebaliknya, `$request->all()` mengembalikan seluruh data mentah dari payload request, yang membuka celah fatal *Mass Assignment* jika model Eloquent memiliki konfigurasi fillable yang longgar.

- [x] **Jelaskan pola PRG. Apa yang terjadi kalau `store` mengembalikan view?**
  *Jawaban:* PRG singkatan dari **Post-Redirect-Get**. Alurnya: Client mengirim request `POST` untuk mengubah data → Server memproses dan mengembalikan respon `REDIRECT` (302) → Browser otomatis melakukan request `GET` ke halaman tujuan. Jika `store` langsung mengembalikan view (`return view()`), URL browser masih memegang status method `POST`. Ketika pengguna me-refresh halaman (menekan F5), browser akan mengulang pengiriman request `POST` tersebut dan menyebabkan transaksi data tersimpan ganda di database.

- [x] **Kenapa filter pencarian sebaiknya di query string, bukan session? Beri satu skenario yang rusak kalau dipindah ke session.**
  *Jawaban:* Query string (`?q=laravel&status=active`) bersifat *stateless* dan menempel pada URL spesifik. 
  *Skenario yang rusak:* Jika filter disimpan di session, saat pengguna membuka dua tab berbeda (misal Tab 1 melihat mata kuliah Semester 1 dan Tab 2 melihat mata kuliah Semester 5), filter di Tab 2 akan menimpa session Tab 1. Saat Tab 1 di-refresh, tampilannya berubah mengikuti filter Tab 2. Selain itu, tautan hasil pencarian tidak bisa di-copy untuk dibagikan ke orang lain dan tombol *Back* browser menjadi tidak sinkron.

- [x] **Apa fungsi `@csrf`? Serangan apa yang dicegahnya, dan bagaimana serangan itu bekerja?**
  *Jawaban:* `@csrf` berfungsi menghasilkan token rahasia yang unik per-sesi pengguna untuk mencegah serangan **Cross-Site Request Forgery (CSRF)**. Serangan CSRF bekerja dengan memanfaatkan kredensial/cookie sesi korban yang sedang aktif: penyerang memancing korban membuka situs berbahaya yang di dalamnya terdapat script tersembunyi untuk mengirim request POST (misal transfer saldo atau hapus data) ke aplikasi target. Karena browser otomatis melampirkan cookie korban, aplikasi target akan mengira request tersebut sah jika tidak ada verifikasi token CSRF.

- [x] **Kenapa aturan `unique` pada update perlu `ignore()`?**
  *Jawaban:* Saat operasi update, data yang sedang diedit sudah ada di database dengan kode tertentu. Jika kita mengecek keunikan secara polos (`unique:courses,code`), query database akan menemukan data itu sendiri dan menganggapnya duplikat, sehingga proses update gagal. Dengan menambahkan `->ignore($course->id)`, Laravel menambahkan kondisi `WHERE id != :id` pada query pengecekan keunikan, sehingga record yang sedang diperbarui dikecualikan dari pemeriksaan.
