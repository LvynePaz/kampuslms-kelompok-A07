## READ
1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.
3. Jawab: kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?
4. Jawab: kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?

## BREAK

| #  |Yang dicoba|Yang harus Anda amati|Yang saya pelajari|
|---|---|---|---|
|1|Hapus `unique(['course_id','user_id'])` dari course_user, lalu daftarkan mahasiswa yang sama dua kali|Data ganda lolos tanpa keluhan|Unique constraint digunakan untuk mencegah data yang sama tersimpan lebih dari satu kali. Sehingga, jika `unique(['course_id','user_id'])` dihapus, mahasiswa yang sama bisa terdaftar pada mata kuliah yang sama secara berulang|
|2|Tambahkan role ke `$fillable` model User, lalu kirim request pembuatan user dengan role=admin lewat form yang **tidak punya field role**|Mass assignment nyata — Anda baru saja jadi admin|Mass assignment dapat menjadi masalah keamanan jika field sensitif seperti role dimasukkan ke `$fillable`. Akibatnya, user dapat mengirim nilai `role=admin` meskipun field tersebut tidak tersedia di form, sehingga bisa terjadi perubahan hak akses secara tidak sengaja atau bahkan menjadi celah keamanan|
|3|Ganti seluruh `$fillable` dengan `protected $guarded = [];` lalu ulangi nomor 2|Kenapa `$guarded kosong` dilarang|Hal tersebut karena `$guarded = []` berarti semua atribut model boleh diisi melalui mass assignment. Karena itu, penggunaan `$guarded` kosong berisiko karena field penting seperti role dapat diubah tanpa pembatasan|
|4|Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh`|Migrasi tidak reversible = CI merah|Method `down()` pada migration penting untuk rollback. Jika `down()` dikosongkan, migration tidak dapat dikembalikan dengan benar saat menjalankan `migrate:refresh`. Ini menunjukkan bahwa setiap migration sebaiknya memiliki proses rollback yang jelas agar perubahan database tetap dapat dikontrol|
|5|Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen|Kehilangan data berantai|`cascadeOnDelete` dan `restrictOnDelete` memiliki dampak yang berbeda. `restrictOnDelete` mencegah data dosen dihapus jika masih digunakan oleh data lain, sedangkan `cascadeOnDelete` akan ikut menghapus data yang memiliki hubungan dengan dosen tersebut. Karena itu, penggunaan cascade harus hati-hati agar data terkait tidak ikut terhapus tanpa sengaja|

## CHECKPOINT MINGGU 3

- [ ] Tunjukkan migrasi yang **Anda** tulis. Jelaskan setiap constraint di dalamnya.
- [ ] Kenapa `course_user` punya unique composite? Peragakan apa yang terjadi kalau dihapus.
- [ ] Apa itu mass assignment? Tunjukkan di kode Anda apa yang mencegahnya, lalu peragakan serangannya dengan `curl`.
- [ ] Kenapa `role` tidak boleh ada di `$fillable`? Di mana ia diisi sebagai gantinya?
- [ ] Kenapa `lecturer_id` memakai `restrictOnDelete` sementara `materials.course_id` memakai `cascadeOnDelete`?
- [ ] Jalankan `php artisan migrate:refresh` di depan penguji. Harus berhasil tanpa error.
- [ ] Tunjukkan satu bagian kode yang Anda tulis dengan bantuan AI. Apa yang Anda ubah dari keluaran aslinya, dan kenapa?