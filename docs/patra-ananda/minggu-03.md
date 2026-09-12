# Catatan Individu Minggu - 03 - Patra Ananda (10241061)


## 3. READ - BREAK - FIX - BUILD 

3.1 READ 

1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.

    *(Catatan: Sketsa fisik manual digambar di kertas/papan tulis bersama kelompok untuk melatih mental model relasi sebelum koding. Foto sketsa fisik dilampirkan setelah digambar).*

    #### B. Kode Diagram Relasi (Mermaid ERD)
    Berikut kode diagram ERD sesuai dengan spesifikasi KampusLMS (Bagian 4):

    ```mermaid
    erDiagram
        users ||--o{ course_user : "terdaftar"
        users ||--o{ courses : "mengajar"
        users ||--o{ submissions : "mengumpulkan"
        users ||--o{ materials : "mengunggah"
        users ||--o{ notifications : "menerima"

        courses ||--o{ course_user : "punya peserta"
        courses ||--o{ materials : "punya"
        courses ||--o{ assignments : "punya"

        assignments ||--o{ submissions : "dikumpulkan"
        submissions ||--o| grades : "dinilai"
        users ||--o{ grades : "memberi nilai"

        users {
            bigint id PK
            string name
            string email UK
            string password
            enum role "admin|dosen|mahasiswa"
            string nim_nip UK "nullable"
            timestamp email_verified_at
            timestamps created_updated
            softdeletes deleted_at
        }

        courses {
            bigint id PK
            string code UK "contoh SI2514024"
            string name
            text description
            tinyint sks
            bigint lecturer_id FK "users.id"
            enum status "draft|active|archived"
            timestamps created_updated
        }

        course_user {
            bigint id PK
            bigint course_id FK
            bigint user_id FK
            timestamp enrolled_at
            timestamps created_updated
        }

        materials {
            bigint id PK
            bigint course_id FK
            bigint uploaded_by FK "users.id"
            string title
            text description
            enum type "file|link"
            string file_path "nullable"
            string original_name "nullable"
            unsignedbigint file_size "nullable"
            string mime_type "nullable"
            string external_url "nullable"
            timestamps created_updated
        }

        assignments {
            bigint id PK
            bigint course_id FK
            bigint created_by FK "users.id"
            string title
            text instructions
            datetime due_at
            unsignedtinyint max_score "default 100"
            boolean allow_late "default true"
            enum status "draft|published"
            timestamps created_updated
        }

        submissions {
            bigint id PK
            bigint assignment_id FK
            bigint user_id FK
            string file_path
            string original_name
            unsignedbigint file_size
            text note "nullable"
            datetime submitted_at
            boolean is_late "default false"
            timestamps created_updated
        }

        grades {
            bigint id PK
            bigint submission_id FK "unique - 1 submission 1 nilai"
            bigint graded_by FK "users.id"
            decimal score
            text feedback "nullable"
            datetime graded_at
            timestamps created_updated
        }

        notifications {
            uuid id PK
            string type
            string notifiable_type
            bigint notifiable_id
            text data
            timestamp read_at
            timestamps created_updated
        }
    ```


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


3.2 BREAK 

