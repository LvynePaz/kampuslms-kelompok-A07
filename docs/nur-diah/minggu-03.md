## READ
1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
   ![alt text](<erd kampuslms.jpeg>)

2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.  

|Foreign Key|Perilaku|Alasan|  
|---|---|---|  
|`courses.lecturer_id` → `users.id`|**restrictOnDelete**|Course, materials, assignments, submissions, dan grades semuanya berantai ke `courses`. Kalau dosen dihapus lalu course ikut cascade, seluruh histori akademik mahasiswa (nilai, tugas) ikut lenyap. Jadi hapus dosen harus **ditolak** dulu selama dia masih punya course aktif — admin wajib pindahkan/reassign dulu| 
| `course_user.course_id` → `courses.id` | **cascadeOnDelete** | Baris `course_user` cuma catatan "siapa terdaftar di course apa". Kalau course-nya sendiri dihapus, catatan pendaftaran itu otomatis tidak relevan lagi. |  
| `course_user.user_id` → `users.id` | **cascadeOnDelete** | Sama seperti di atas — kalau akun mahasiswa dihapus, catatan keanggotaannya di course tidak berguna lagi disimpan sendirian. |  
| `materials.course_id` → `courses.id` | **cascadeOnDelete** | Materi cuma bermakna dalam konteks course tertentu; hapus course = materi ikut tidak relevan. |  
| `materials.uploaded_by` → `users.id` | **restrictOnDelete** | Kolom ini adalah jejak siapa yang mengunggah (accountability). Kalau user dihapus lalu materialnya ikut cascade/null, jejak akademik hilang. Lebih aman ditolak dulu, dosen/admin harus tangani datanya dulu sebelum akun dihapus. |  
| `assignments.course_id` → `courses.id` | **cascadeOnDelete** | Sama logikanya dengan materials — tugas tanpa course induk tidak ada artinya. |  
| `assignments.created_by` → `users.id` | **restrictOnDelete** | Assignment yang dibuat dosen ini punya rantai lanjutan ke `submissions` dan `grades` mahasiswa. Kalau dihapus cascade, nilai mahasiswa ikut lenyap — jadi harus restrict. |  
| `submissions.assignment_id` → `assignments.id` | **cascadeOnDelete** | Submission cuma bermakna sebagai jawaban dari assignment tertentu; hapus assignment = submission ikut tidak relevan. |  
| `submissions.user_id` → `users.id` | **restrictOnDelete** | Submission adalah bukti kerja akademik mahasiswa dan jadi dasar nilai (`grades`). Ini harus dipertahankan sebagai arsip/audit trail, jadi hapus akun mahasiswa ditolak selama masih ada submission miliknya. |  
| `grades.submission_id` → `submissions.id` | **cascadeOnDelete** | Grade adalah anak langsung dari satu submission spesifik (relasi 1:1) — kalau submission-nya dihapus, nilainya otomatis kehilangan makna dan boleh ikut terhapus. |  
| `grades.graded_by` → `users.id` | **restrictOnDelete** | Kolom ini jejak akuntabilitas siapa dosen yang memberi nilai. Hapus akun dosen penilai tidak boleh diam-diam menghapus/mengosongkan histori penilaian — harus ditolak dulu. |  

3. Jawab: kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?  
Tidak terjadi apa-apa pada course-nya — **penghapusan dosennya sendiri yang gagal/ditolak** oleh database, karena `lecturer_id` memakai `restrictOnDelete`. Dengan memakai `cascadeOnDelete`, menghapus satu akun dosen akan otomatis menghapus semua course yang dia ampu dan karena `assignments`, `submissions`, `grades` semuanya cascade dari `courses`, efek dominonya bisa menghapus seluruh nilai dan tugas mahasiswa hanya karena satu dosen resign/dinonaktifkan. Itu kerugian data yang sangat besar dan tidak masuk akal secara bisnis. Dengan `restrictOnDelete`, sistem memaksa admin untuk **memindahkan dulu** course tersebut ke dosen lain (update `lecturer_id`) sebelum akun dosen lama boleh dihapus — data akademik tetap aman.

4. Jawab: kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?  
Karena relasinya memang **1:1** — satu submission cuma boleh punya **tepat satu** nilai. Index biasa hanya mempercepat pencarian, tapi tidak mencegah duplikasi data. Kalau cuma memakai index biasa, aplikasi bisa saja (sengaja atau karena bug/race condition) meng-insert dua baris grade untuk `submission_id` yang sama — akan ada dua nilai berbeda untuk satu jawaban mahasiswa, yang jelas tidak masuk akal dan membuat ambigu nilai mana yang valid. Constraint `unique` memaksa aturan bisnis ini ditegakkan **di level database**, bukan cuma diandalkan dari validasi controller yang bisa bocor (mirip kasus `course_user` pada eksperimen sebelumnya).

## BREAK

| #  |Yang dicoba|Yang harus Anda amati|Yang saya pelajari|
|---|---|---|---|
|1|Hapus `unique(['course_id','user_id'])` dari course_user, lalu daftarkan mahasiswa yang sama dua kali|Data ganda lolos tanpa keluhan|Unique constraint digunakan untuk mencegah data yang sama tersimpan lebih dari satu kali. Sehingga, jika `unique(['course_id','user_id'])` dihapus, mahasiswa yang sama bisa terdaftar pada mata kuliah yang sama secara berulang|
|2|Tambahkan role ke `$fillable` model User, lalu kirim request pembuatan user dengan role=admin lewat form yang **tidak punya field role**|Mass assignment nyata — Anda baru saja jadi admin|Mass assignment dapat menjadi masalah keamanan jika field sensitif seperti role dimasukkan ke `$fillable`. Akibatnya, user dapat mengirim nilai `role=admin` meskipun field tersebut tidak tersedia di form, sehingga bisa terjadi perubahan hak akses secara tidak sengaja atau bahkan menjadi celah keamanan|
|3|Ganti seluruh `$fillable` dengan `protected $guarded = [];` lalu ulangi nomor 2|Kenapa `$guarded kosong` dilarang|Hal tersebut karena `$guarded = []` berarti semua atribut model boleh diisi melalui mass assignment. Karena itu, penggunaan `$guarded` kosong berisiko karena field penting seperti role dapat diubah tanpa pembatasan|
|4|Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh`|Migrasi tidak reversible = CI merah|Method `down()` pada migration penting untuk rollback. Jika `down()` dikosongkan, migration tidak dapat dikembalikan dengan benar saat menjalankan `migrate:refresh`. Ini menunjukkan bahwa setiap migration sebaiknya memiliki proses rollback yang jelas agar perubahan database tetap dapat dikontrol|
|5|Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen|Kehilangan data berantai|`cascadeOnDelete` dan `restrictOnDelete` memiliki dampak yang berbeda. `restrictOnDelete` mencegah data dosen dihapus jika masih digunakan oleh data lain, sedangkan `cascadeOnDelete` akan ikut menghapus data yang memiliki hubungan dengan dosen tersebut. Karena itu, penggunaan cascade harus hati-hati agar data terkait tidak ikut terhapus tanpa sengaja|

## FIX


## CHECKPOINT MINGGU 3

- [ ] Tunjukkan migrasi yang **Anda** tulis. Jelaskan setiap constraint di dalamnya.
- [ ] Kenapa `course_user` punya unique composite? Peragakan apa yang terjadi kalau dihapus.
- [ ] Apa itu mass assignment? Tunjukkan di kode Anda apa yang mencegahnya, lalu peragakan serangannya dengan `curl`.
- [ ] Kenapa `role` tidak boleh ada di `$fillable`? Di mana ia diisi sebagai gantinya?
- [ ] Kenapa `lecturer_id` memakai `restrictOnDelete` sementara `materials.course_id` memakai `cascadeOnDelete`?
- [ ] Jalankan `php artisan migrate:refresh` di depan penguji. Harus berhasil tanpa error.
- [ ] Tunjukkan satu bagian kode yang Anda tulis dengan bantuan AI. Apa yang Anda ubah dari keluaran aslinya, dan kenapa?