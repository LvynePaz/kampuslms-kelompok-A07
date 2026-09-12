*READ*
1.  
2. 
|Foreign key |``onDelete``|Alasan|
|------------|------------|------|
|``courses.lecturer_id``|``SETNULL``|Mata kuliah tetap penting sebagai data akademik meskipun dosennya dihapus. Kolom harus nullable.|
``assignments.course_id``|``CASCADE``|Tugas tidak bermakna tanpa mata kuliah induknya. Jika mata kuliah dihapus, tugasnya ikut dihapus.|
``submissions.assignment_id``|``CASCADE``|Submission hanya ada karena tugas tertentu. Jika tugas dihapus, submission terkait juga harus dihapus.|
``submissions.student_id``|``RESTRICT``|Data submission dan histori akademik tidak boleh hilang hanya karena akun mahasiswa dihapus. Lebih aman menonaktifkan akun atau melakukan soft delete.
``grades.submission_id``|``CASCADE``|Nilai tidak bermakna tanpa submission. Jika submission dihapus, nilai terkait ikut dihapus.

3. Mata kuliahnya tetap ada, tetapi ``courses.lecturer_id`` diubah menjadi ``NULL``.

Alasannya, dosen dan mata kuliah adalah entitas yang berbeda. Penghapusan dosen tidak berarti mata kuliah, tugas, submission, dan histori nilai harus ikut hilang. Mata kuliah tersebut nantinya dapat diberikan kepada dosen pengganti.

4. Karena satu submission hanya boleh memiliki satu nilai.

``index`` biasa hanya mempercepat pencarian, tetapi masih mengizinkan data seperti:

submission_id | score
--------------|------
10            | 80
10            | 90

Dengan ``unique``, database mencegah submission yang sama diberi dua nilai:

$table->foreignId('submission_id')
    ->unique()
    ->constrained()
    ->cascadeOnDelete();

Dengan demikian, relasi ``submissions`` ke ``grades`` adalah one-to-zero-or-one: sebuah submission boleh belum dinilai, tetapi setelah dinilai hanya boleh memiliki satu record nilai.

*BREAK*
|#|Yang dicoba|Yang harus anda diamati|
|-|-----------|-----------------------|
|1|Hapus ``unique(['course_id','user_id'])`` dari ``course_user``, lalu daftarkan mahasiswa yang sama dua kali|Data ganda lolos tanpa keluhan|
|2|Tambahkan ``role`` ke ``$fillable`` model ``User``, lalu kirim request pembuatan user dengan role=admin lewat form yang tidak punya field role|Mass assignment nyata — Anda baru saja jadi admin
|3|Ganti seluruh ``$fillable`` dengan ``protected $guarded = [];`` lalu ulangi nomor 2|Kenapa ``$guarded`` kosong dilarang
|4|Kosongkan isi ``down()`` di satu migrasi, lalu jalankan ``php artisan migrate:refresh``|Migrasi tidak reversible = CI merah
|5|Ubah ``restrictOnDelete`` pada ``lecturer_id`` menjadi ``cascadeOnDelete``, lalu hapus satu dosen|Kehilangan data berantai

*Jawab* 
1. 