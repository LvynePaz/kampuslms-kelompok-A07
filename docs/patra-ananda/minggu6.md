# Catatan Individu Minggu - 06 - Patra Ananda (10241061)

## 6.3 READ — BREAK — FIX — BUILD

### 1. READ — Bandingkan Dua Jalur (Web vs API) (30 menit)

#### 1. Jalankan `php artisan install:api` dan Baca Perubahan di `bootstrap/app.php`
Pada Laravel 12, arsitektur default dibuat ramping (*lean*). Berkas `routes/api.php` tidak disediakan secara otomatis saat inisialisasi awal proyek.

Perintah aktivasi:
```bash
php artisan install:api
```

Perubahan otomatis yang terjadi pada sistem:
1. **`bootstrap/app.php`**: Routing API didaftarkan otomatis ke dalam container aplikasi dengan prefix bawaan `/api`:
   ```php
   ->withRouting(
       web: __DIR__.'/../routes/web.php',
       api: __DIR__.'/../routes/api.php', // Ditambahkan otomatis
       commands: __DIR__.'/../routes/console.php',
       health: '/up',
   )
   ```
2. **Paket Laravel Sanctum**: Otomatis terpasang untuk menangani autentikasi berbasis Bearer Token.
3. **Migrasi Database**: Dibuatkan file migrasi tabel `personal_access_tokens` untuk menyimpan riwayat token klien.
4. **Berkas Baru**: Berkas `routes/api.php` dibuat sebagai tempat mendefinisikan seluruh endpoint REST API.

---

#### 2. Buat Satu Endpoint `GET /api/v1/courses` Sederhana
Menambahkan rute sederhana pada `routes/api.php` untuk mengembalikan data mata kuliah:

```php
use App\Models\Course;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/courses', function () {
        return Course::with('lecturer')->paginate(15);
    });
});
```

*Pengamatan:* Ketika endpoint `http://localhost:8000/api/v1/courses` diakses via browser atau cURL, server langsung mengembalikan koleksi data mata kuliah dalam format JSON lengkap dengan relasi dosen dan metadata pagination tanpa melibatkan template HTML Blade.

---

#### 3. Bandingkan dengan `CourseController` Versi Web (Apa yang Sama & Apa yang Berbeda)
Perbandingan antara endpoint API `GET /api/v1/courses` dengan `CourseController@index` versi Web:

| Aspek | Yang **SAMA** | Yang **BERBEDA** |
|---|---|---|
| **Model & Query Database** | Keduanya mengeksekusi model Eloquent yang sama (`Course`), memakai eager loading yang sama (`with('lecturer')`), serta logika query database yang identik. | — |
| **Logika Paginasi & Filter** | Paginasi data (`paginate(15)`), aturan pencarian data, dan relasi tabel tetap sama di level database. | — |
| **Format Respons** | — | **Web:** Mengembalikan tampilan HTML siap saji via `return view('courses.index', ...)`.<br>**API:** Mengembalikan data terstruktur murni berupa JSON. |
| **State & Autentikasi** | — | **Web:** *Stateful* menggunakan Session dan Cookie browser (`laravel_session`).<br>**API:** *Stateless*, autentikasi melalui header `Authorization: Bearer <token>`. |
| **Proteksi CSRF** | — | **Web:** Wajib menyertakan token CSRF (`@csrf`) pada form untuk mencegah serangan *Cross-Site Request Forgery*.<br>**API:** Tidak memerlukan token CSRF karena API bersifat stateless dan tidak mengandalkan cookie sesi browser. |
| **Penanganan Gagal Akses** | — | **Web:** Redirect HTTP `302 Found` ke laman login (`/login`).<br>**API:** Mengembalikan status kode HTTP `401 Unauthorized` atau `403 Forbidden` dalam format JSON. |

*Kesimpulan Arsitektur:* Backend memisahkan penyajian tampilan dari penyediaan data. Logika database dan query tetap dapat dipakai ulang tanpa perlu diubah, namun saluran distribusinya (Web vs API) memiliki karakteristik arsitektur dan penanganan keamanan yang berbeda.

---

#### 4. Panggil Endpoint API Tanpa dan Dengan Header `Accept: application/json`
Menguji endpoint API menggunakan cURL dengan dan tanpa header `Accept`:

1. **Tanpa Header `Accept`:**
   ```bash
   curl -i http://localhost:8000/api/v1/courses
   ```
   *Pengamatan:* Jika terjadi error validasi atau belum login, Laravel default berasumsi klien meminta HTML sehingga merespons dengan **redirect 302 ke `/login`** atau mengembalikan laman HTML debug.

2. **Dengan Header `Accept: application/json`:**
   ```bash
   curl -i http://localhost:8000/api/v1/courses -H "Accept: application/json"
   ```
   *Pengamatan:* Server secara eksplisit merespons data murni dalam format **JSON** dengan status code yang presisi (misal `401 Unauthorized` dalam payload JSON: `{"message": "Unauthenticated."}`). Header ini wajib disertakan oleh klien API.

---

#### 5. Peta Route API (`php artisan route:list --path=api`)
Memverifikasi kesesuaian endpoint dengan Bagian 5 Spesifikasi Proyek KampusLMS:

```
+--------+-----------+------------------------------------+--------------------------+
| Method | URI       | Name                               | Action                   |
+--------+-----------+------------------------------------+--------------------------+
| POST   | api/v1/auth/login     | api.v1.auth.login      | AuthController@login     |
| POST   | api/v1/auth/logout    | api.v1.auth.logout     | AuthController@logout    |
| GET    | api/v1/me             | api.v1.me              | AuthController@me        |
| GET    | api/v1/courses        | api.v1.courses.index   | CourseController@index   |
| GET    | api/v1/courses/{id}   | api.v1.courses.show    | CourseController@show    |
| GET    | api/v1/courses/{id}/materials   | api.v1.courses.materials   | CourseController@materials   |
| GET    | api/v1/courses/{id}/assignments | api.v1.courses.assignments | CourseController@assignments |
| POST   | api/v1/assignments              | api.v1.assignments.store   | AssignmentController@store   |
| PUT    | api/v1/assignments/{id}         | api.v1.assignments.update  | AssignmentController@update  |
| DELETE | api/v1/assignments/{id}         | api.v1.assignments.destroy | AssignmentController@destroy |
+--------+-----------+------------------------------------+--------------------------+
```

---

### 2. BREAK — Tujuh Kerusakan (45 menit)

Tabel inventarisasi eksperimen kegagalan dan eksploitasi API:

| # | Yang Dicoba | Yang Diamati & Bahaya Keamanannya |
|---|---|---|
| **1** | Mengembalikan model mentah `return response()->json(User::all())` pada endpoint uji | **Hash password bocor di JSON:** Seluruh atribut model keluar tanpa filter, termasuk hash `password`, `remember_token`, dan email seluruh pengguna. Ini merupakan kebocoran data (*information disclosure*) paling fatal akibat mengabaikan API Resource. |
| **2** | Menghapus middleware `auth:sanctum` dari grup route API, lalu panggil tanpa token | **Data privat terbuka untuk publik (200 OK):** Klien anonim tanpa token dapat membaca seluruh data internal kampus tanpa batas. |
| **3** | Memanggil endpoint berpelindung token dengan token yang sudah di-revoke / dihapus di database | **401 Unauthorized:** Sanctum gagal memverifikasi ID token di tabel `personal_access_tokens` dan langsung menolak request dengan payload `{"message": "Unauthenticated."}`. |
| **4** | Login sebagai mahasiswa, lalu mencoba panggil `POST /api/v1/assignments` (fitur dosen) | **Wajib 403 Forbidden:** Mahasiswa sudah terautentikasi (bukan 401), namun tidak memiliki izin membuat tugas. Jika menghasilkan 401 atau lolos 200, berarti layer otorisasi peran (*RBAC / Policy*) belum terpasang. |
| **5** | Menghapus eager loading (`with(...)`) pada pemanggilan daftar mata kuliah yang menyertakan data dosen | **Ledakan Query N+1 di Background:** Untuk 50 mata kuliah, sistem menjalankan 51 query database terpisah (`1 + N`). Di API, payload terlihat normal tetapi latency server membengkak drastis. |
| **6** | Menghapus middleware `throttle:5,1` pada endpoint login, lalu kirim 50 request berulang | **Brute-Force Tanpa Batas:** Penyerang dapat menjalankan skrip otomatis untuk menebak ribuan password per menit tanpa blokir dari server. |
| **7** | Mengembalikan pesan login berbeda ("Email tidak terdaftar" vs "Password salah") | ***User Enumeration*:** Penyerang dapat memetakan daftar email civitas kampus yang valid hanya dengan mengamati perbedaan pesan error yang dikembalikan server. |

---

### 3. FIX — Perbaikan Repo Cacat (Branch `w06` pada `kampuslms-broken`)

Delapan cacat sistem pada branch `w06` yang dianalisis dan diperbaiki:

1. **Model Mentah pada Response User & Course:**
   * *Masalah:* `return response()->json(User::all())` membocorkan kolom hash password.
   * *Solusi:* Membungkus seluruh output menggunakan API Resource (`UserResource` dan `CourseResource`).

2. **Endpoint Terbuka Tanpa Proteksi Sanctum:**
   * *Masalah:* Route `/api/v1/courses` tidak memiliki middleware pengaman.
   * *Solusi:* Memasukkan route ke dalam grup `Route::middleware('auth:sanctum')`.

3. **Status Code Salah pada Operasi `store`:**
   * *Masalah:* Endpoint pembuatan data baru merespons dengan status `200 OK`.
   * *Solusi:* Mengubah response menjadi HTTP `201 Created` disertai data objek yang baru dibuat.

4. **Status Code Salah pada Operasi `destroy`:**
   * *Masalah:* Endpoint hapus merespons teks JSON dengan status `200 OK`.
   * *Solusi:* Mengubah status menjadi HTTP `204 No Content` tanpa body payload.

5. **Pelanggaran Status Otorisasi (403 Tertukar Menjadi 401):**
   * *Masalah:* Mahasiswa mengakses resource dosen menghasilkan respon `401 Unauthenticated`.
   * *Solusi:* Memperbaiki error handling menjadi `403 Forbidden` karena token mahasiswa valid, hanya saja hak aksesnya tidak mencukupi.

6. **Endpoint Login Tanpa Rate Limiting:**
   * *Masalah:* Route login dapat di-spam terus-menerus.
   * *Solusi:* Menambahkan middleware `throttle:5,1` (maksimal 5 kali percobaan gagal per menit).

7. **Celah *User Enumeration* pada Pesan Autentikasi:**
   * *Masalah:* Sistem menampilkan pesan "Email tidak ditemukan".
   * *Solusi:* Menyeragamkan pesan kegagalan menjadi satu: `"Email atau kata sandi salah."` baik saat email tidak ada maupun password salah.

8. **N+1 Query pada Daftar Mata Kuliah:**
   * *Masalah:* Relasi `lecturer` dan hitungan `materials` dipanggil secara lazy-loading di dalam resource loop.
   * *Solusi:* Memasang eager loading pada controller: `Course::with('lecturer')->withCount(['materials', 'assignments'])->paginate(15)` dan menggunakan `$this->whenLoaded()` serta `$this->whenCounted()` di Resource.

#### Bukti Pengujian via cURL:

* **Sebelum Perbaikan (Model Mentah Bocor):**
  ```bash
  curl -X GET http://localhost:8000/api/v1/users/1 -H "Accept: application/json"
  # Respons: HTTP 200 OK
  # Payload: {"id":1,"name":"Admin","email":"admin@kampuslms.test","password":"$2y$12$e8h..."} (Hash password bocor!)
  ```

* **Setelah Perbaikan (API Resource Berjalan & Aman):**
  ```bash
  curl -X GET http://localhost:8000/api/v1/users/1 -H "Accept: application/json" -H "Authorization: Bearer 1|token..."
  # Respons: HTTP 200 OK
  # Payload: {"data":{"id":1,"name":"Admin","email":"admin@kampuslms.test","role":"admin"}} (Data sensitif terfilter)
  ```

* **Uji Rate Limiting Login (Percobaan ke-6):**
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/login \
    -H "Accept: application/json" \
    -d "email=dosen@kampuslms.test" -d "password=salah"
  # Respons: HTTP 429 Too Many Requests
  # Payload: {"message":"Too Many Attempts."}
  ```

---

### 4. BUILD — REST API KampusLMS

Implementasi REST API pada repositori `kampuslms-kelompok-A07`:

1. **Aktivasi Sanctum & Routing:**
   * Menjalankan `php artisan install:api`, mengaktifkan rute `routes/api.php`, dan menjalankan migrasi tabel token personal.
2. **Implementasi API Resource:**
   * Membuat layer transformasi data: `UserResource`, `CourseResource`, `MaterialResource`, `AssignmentResource`, `SubmissionResource`, dan `GradeResource`.
   * Menjaga prinsip *whitelist*: hanya field publik yang dikembalikan.
   * Menerapkan `whenLoaded()` dan `whenCounted()` untuk relasi agar mencegah masalah performa N+1.
3. **Penyelarasan Kontrak API (Bagian 5 Spesifikasi):**
   * Format koleksi data otomatis menyertakan wrapper `data` dan paginasi `meta`.
   * Penilaian tugas (`PUT /api/v1/submissions/{id}/grade`) menggunakan logika `updateOrCreate`: merespons `201 Created` saat penilaian pertama dan `200 OK` saat pembaruan nilai.
4. **Proteksi & Keamanan:**
   * Menerapkan middleware `auth:sanctum` untuk seluruh rute privat.
   * Menerapkan pembatasan rate limit: `throttle:60,1` untuk request umum dan `throttle:5,1` untuk `/api/v1/auth/login`.
5. **Dokumentasi & Skrip Pengujian:**
   * Dokumentasi endpoint dicatat pada `docs/api.md`.
   * Skrip otomatisasi pengujian otorisasi dibuat pada `scripts/test-api.sh` untuk menguji skenario tanpa token, token mahasiswa, dan token dosen.
6. **Keputusan Jalur Frontend:**
   * Kelompok A07 mendaftarkan pilihan jalur frontend untuk minggu 7–16 kepada dosen.

---

## 6.4 Checkpoint Minggu 6

### 1. Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.
* **Penyebab:** Model Eloquent secara default memetakan seluruh kolom yang ada pada tabel database. Jika model dikembalikan langsung via `response()->json($model)`, fungsi serializer akan mengubah seluruh atribut tabel menjadi JSON—termasuk field privat dan sensitif seperti hash kata sandi (`password`), remember token, token reset, maupun kolom internal sistem yang tidak relevan bagi klien.
* **Peragaan Kebocoran:**
  ```php
  // Kode controller yang salah:
  return response()->json(User::first());
  ```
  Output JSON yang bocor ke publik:
  ```json
  {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@kampuslms.test",
    "password": "$2y$12$G9g9Z8Z3vB3h6qP1j6Qv7.eK...", 
    "remember_token": "aX9Lz10Qpw...",
    "created_at": "2026-03-01T10:00:00.000000Z"
  }
  ```
* **Solusi Wajib:** Gunakan **API Resource** (`JsonResource`) sebagai *whitelist* penentu data apa saja yang sah dilihat oleh klien.

---

### 2. Apa beda 401 dan 403? Tunjukkan di API Anda satu contoh masing-masing.
* **401 Unauthorized (Unauthenticated):** Klien **belum membuktikan siapa dirinya** (tidak ada token, token kedaluwarsa, atau token salah). Server tidak tahu siapa yang melakukan request.
  * *Contoh di KampusLMS:* Pengguna memanggil `GET /api/v1/courses` tanpa menyertakan header `Authorization: Bearer <token>`. Server merespons `401 Unauthorized`.
* **403 Forbidden (Unauthorized):** Server **tahu siapa pengguna tersebut**, tetapi pengguna tersebut **tidak memiliki hak izin** untuk mengakses data atau tindakan tersebut.
  * *Contoh di KampusLMS:* Mahasiswa login secara sah dan membawa token valid, lalu memanggil `POST /api/v1/assignments` (membuat tugas). Karena peran mahasiswa tidak berhak membuat tugas, server merespons `403 Forbidden`.

---

### 3. Kenapa `routes/api.php` tidak ada secara default di Laravel 12? Bagaimana mengaktifkannya?
* **Alasan Desain Laravel 12:** Laravel 12 mengadopsi pendekatan minimalis (*lean architecture*). Banyak aplikasi web berbasis monolitik penuh yang hanya membutuhkan Blade tanpa REST API. Menghilangkan konfigurasi API secara default mempercepat *bootstrapping* aplikasi dan menjaga struktur direktori tetap bersih dari berkas yang tidak digunakan.
* **Cara Mengaktifkannya:** Jalankan perintah Artisan:
  ```bash
  php artisan install:api
  ```
  Perintah ini otomatis menginstal Sanctum, membuat file `routes/api.php`, membuat migrasi tabel token, dan mendaftarkan rute API di dalam `bootstrap/app.php`.

---

### 4. Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?
* **Fungsi:** `whenLoaded('namaRelasi')` pada API Resource memastikan bahwa data relasi Eloquent **hanya disertakan ke dalam JSON jika relasi tersebut sudah di-load sebelumnya** (misalnya lewat `with('namaRelasi')` di controller).
* **Konsekuensi Tanpa `whenLoaded()`:** Jika relasi dipanggil langsung tanpa kondisi (misal: `'lecturer' => new UserResource($this->lecturer)`), Laravel akan secara otomatis menjalankan query database tambahan untuk mengambil data relasi pada *setiap baris item*. Hal ini memicu bencana performa **N+1 Query**, di mana jika ada 50 mata kuliah, sistem akan mengeksekusi 51 kali query ke database.

---

### 5. Kenapa pesan gagal login tidak boleh membedakan email salah dan password salah?
* **Mencegah Celah *User Enumeration* (Pencacahan Pengguna):**
  * Jika server menampilkan *"Email tidak terdaftar"*, penyerang tahu bahwa email tersebut tidak ada di sistem.
  * Jika server menampilkan *"Kata sandi salah"*, penyerang mendapat konfirmasi pasti bahwa **email tersebut benar-benar terdaftar di sistem**.
* Penyerang dapat memanfaatkan perbedaan ini untuk membuat kamus berisi ribuan email civitas kampus yang valid guna target serangan phishing terarah atau *credential stuffing*.
* Pesan wajib dibuat seragam: **"Email atau kata sandi salah."**

---

### 6. Kenapa endpoint login wajib di-*throttle*? Berapa nilai yang Anda pakai dan mengapa?
* **Alasan Wajib:** Endpoint login adalah pintu gerbang autentikasi. Tanpa pembatasan frekuensi (*rate limiting / throttling*), penyerang dapat menjalankan serangan *brute-force* atau serangan kamus (*dictionary attack*) dengan mengirim ribuan tebakan password per menit menggunakan bot otomatis.
* **Nilai yang Digunakan:** `throttle:5,1` (maksimal **5 percobaan per 1 menit**).
* **Alasannya:**
  1. Memberikan toleransi wajar bagi pengguna manusia yang salah ketik 1–2 kali.
  2. Menghentikan total serangan bot otomatis karena setelah 5 kegagalan, IP pemanggil langsung diblokir sementara dengan HTTP status code `429 Too Many Requests`.
