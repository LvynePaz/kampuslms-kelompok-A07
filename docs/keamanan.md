# Dokumentasi Keamanan & Matriks Pencegahan IDOR — KampusLMS (Kelompok A07)

Dokumen ini memetakan seluruh titik rawan kerentanan **Insecure Direct Object Reference (IDOR)** dan kebocoran otorisasi pada aplikasi **KampusLMS (Laravel 12)**, serta membuktikan mekanisme pengamanan yang diterapkan (Policy & Query Scope) sesuai kontrak spesifikasi Milestone M2 (Tugas 2).

---

## 1. Definisi & Bahaya IDOR pada Sistem LMS

**Insecure Direct Object Reference (IDOR)** terjadi ketika aplikasi menerima parameter identifikasi objek (seperti `id` pada URL `/courses/{id}` atau `/submissions/{id}`) dan langsung menampilkan atau memodifikasi objek tersebut tanpa memverifikasi apakah pengguna yang sedang login memiliki hak atas objek tersebut.

Pada sistem LMS kampus:
* Mahasiswa nakal dapat mengganti angka ID pada URL untuk membaca jawaban tugas atau nilai mahasiswa lain.
* Dosen dapat secara tidak sengaja atau sengaja mengedit materi atau mengubah nilai pada mata kuliah yang diampu oleh dosen lain.

---

## 2. Tabel Matriks Titik Rawan IDOR & Solusi Penutupnya

| No | Titik Rawan / Endpoint | Parameter Objek | Potensi Bahaya (Dampak IDOR) | Mekanisme Pengamanan | Penutup (Policy / Query Filter) | Status |
|:--:|---|---|---|---|---|:--:|
| **1** | `GET /courses` (Daftar Matkul) | Koleksi data | Mahasiswa melihat seluruh mata kuliah kampus, termasuk yang tidak diikutinya. | **Penyaringan di Level Query** | `CourseController@index`<br>`match ($user->role)` | ✅ Tertutup |
| **2** | `GET /courses/{course}` | `Course $course` | Mahasiswa mengakses materi/detail mata kuliah kelas lain. | **Policy Objek** | `CoursePolicy@view`<br>`$course->students()->whereKey($user->id)->exists()` | ✅ Tertutup |
| **3** | `PUT /courses/{course}` | `Course $course` | Dosen A mengedit informasi atau deskripsi mata kuliah milik Dosen B. | **Policy Objek** | `CoursePolicy@update`<br>`$course->lecturer_id === $user->id` | ✅ Tertutup |
| **4** | `DELETE /courses/{course}` | `Course $course` | Dosen atau mahasiswa menghapus mata kuliah dari sistem. | **Role & Policy** | `CoursePolicy@delete`<br>`$user->role === 'admin'` | ✅ Tertutup |
| **5** | `GET /materials/{material}` | `Material $material` | Mahasiswa mengunduh materi privat dari mata kuliah yang tidak diikutinya. | **Policy Objek** | `MaterialPolicy@view`<br>`$material->course->students()->whereKey(...)` | ✅ Tertutup |
| **6** | `POST /courses/{course}/materials` | `Course $course` | Mahasiswa atau dosen lain mengunggah materi ke mata kuliah orang lain. | **Policy Objek** | `MaterialPolicy@create`<br>`$course->lecturer_id === $user->id` | ✅ Tertutup |
| **7** | `GET /assignments/{assignment}` | `Assignment $assignment` | Mahasiswa melihat soal tugas dari kelas lain yang belum dibuka. | **Policy Objek** | `AssignmentPolicy@view`<br>`$assignment->course->students()->whereKey(...)` | ✅ Tertutup |
| **8** | `POST /assignments` | Koleksi data | Mahasiswa membuat tugas kuliah tiruan di portal. | **Role Validation & Policy** | `AssignmentPolicy@create`<br>`in_array($user->role, ['admin', 'dosen'])` | ✅ Tertutup |
| **9** | `GET /submissions/{submission}` | `Submission $submission` | **(Kritis)** Mahasiswa A menyalin file/jawaban tugas milik Mahasiswa B dengan menebak ID. | **Policy Objek** | `SubmissionPolicy@view`<br>`$submission->user_id === $user->id` | ✅ Tertutup |
| **10** | `PUT /submissions/{submission}` | `Submission $submission` | Mahasiswa mengubah jawaban tugas setelah dinilai atau mengubah tugas milik orang lain. | **Policy Objek** | `SubmissionPolicy@update`<br>`!$submission->grade()->exists()` | ✅ Tertutup |
| **11** | `POST /submissions/{id}/grade` | `Submission $submission` | Mahasiswa menilai tugasnya sendiri, atau Dosen A memberi nilai ke mahasiswa Dosen B. | **Policy Objek** | `GradePolicy@create`<br>`$grade->submission->assignment->course->lecturer_id` | ✅ Tertutup |
| **12** | `PATCH /profile` | Data request | Penyerang mengirim payload `role=admin` untuk menaikkan hak akses (*Privilege Escalation*). | **Mass Assignment Whitelist** | `ProfileUpdateRequest`<br>Hanya mengizinkan `name` & `email` | ✅ Tertutup |

---

## 3. Prinsip Pertahanan Berlapis (Defense in Depth)

1. **Bukan Sekadar Menyembunyikan Tombol di Blade (`@can`):**  
   Penyembunyian tombol di antarmuka web hanya bertujuan untuk pengalaman pengguna (*UX*). Keamanan sesungguhnya ditegakkan di backend controller menggunakan:
   ```php
   Gate::authorize('view', $course);
   Gate::authorize('update', $assignment);
   ```
2. **Penyaringan Sejak di Database (Bukan di View):**  
   Daftar koleksi mata kuliah disaring sejak query SQL menggunakan klausa `where('lecturer_id', ...)` untuk dosen dan `whereHas('students', ...)` untuk mahasiswa, mencegah beban memori berlebih dan kebocoran data.
3. **Pemberian Status HTTP yang Tepat:**  
   Setiap kegagalan otorisasi mengembalikan kode status standar **HTTP 403 Forbidden**, tanpa membocorkan identitas pemilik asli maupun rincian data yang ditolak.

---

## 4. Pembuktian dan Pengujian Otomatis

Seluruh aturan otorisasi di atas diverifikasi melalui:
* **Pengujian Fitur Otomatis:** Berkas `tests/Feature/RoleMiddlewareTest.php` dan `tests/Feature/ApiV1Test.php`.
* **Skrip Pengujian Terminal:** Berkas skrip `scripts/test-authz.sh` (eksekusi via Bash/cURL).
