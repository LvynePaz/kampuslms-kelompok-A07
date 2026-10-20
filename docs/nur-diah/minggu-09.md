## READ
1. Pada fitur pengumpulan tugas (submission), `SubmissionController.php` menyimpan file menggunakan fungsi `store()` pada disk local. Sementara itu, `MaterialController.php` menggunakan fungsi yang sama untuk menyimpan materi kuliah.

    Lokasi fisik file submission:
    ```
    <root-project>/storage/app/private/submissions/<assignment-id>/<nama-acak>.<ext>
    ```
    Lokasi fisik file materi:
    ```
    <root-project>/storage/app/private/materials/<nama-acak>.<ext>
    ```
    Jika menggunakan lokasi repository di komputer, bentuk path-nya adalah:
    ```
    D:\GITHUB\kampuslms-kelompok-A07\storage\app\private\materials\<nama-acak>.<ext>
    D:\GITHUB\kampuslms-kelompok-A07\storage\app\private\submissions\<assignment-id>\<nama-acak>.<ext>
    ```
    Nama file fisik dibuat secara acak oleh Laravel, bukan menggunakan nama asli file yang diunggah. Path relatifnya kemudian disimpan ke kolom file_path pada database. Path lengkap yang sebenarnya bergantung pada file yang sudah diunggah dan lokasi project di komputer.

2. Kolom file_path pada database menyimpan lokasi relatif file di dalam disk local, bukan lokasi lengkap file di komputer.  
    | **Kolom database** |      **Contoh nilai**     |                **Fungsi**                |
    |--|--|--|
    | `file_path`        | submissions/12/abc123.pdf | Menunjukkan lokasi relatif file          |
    | `original_name`    | jawaban-tugas.pdf         | Menyimpan nama asli file                 |
    | `file_size`        | 512000                    | Menyimpan ukuran file dalam byte         |
    | `submitted_at`     | Timestamp                 | Menyimpan waktu pengumpulan              |
    | `is_late`          | 0 atau 1                  | Menunjukkan apakah pengumpulan terlambat |

    Untuk menemukan lokasi fisiknya, path relatif digabungkan dengan direktori utama disk local, yaitu storage/app/private. Contohnya:
    ```
    storage/app/private/submissions/12/abc123.pdf
    ```
    Sementara itu, original_name hanya menyimpan nama asli file dan tidak digunakan sebagai lokasi fisik file.

3. File yang baru diunggah melalui disk local seharusnya tidak dapat diakses secara langsung melalui URL publik seperti:
    ```
    /storage/submissions/12/abc123.pdf
    /storage/materials/abc123.pdf
    ```
    Hal ini karena file disimpan di `storage/app/private`, sedangkan symbolic link public/storage mengarah ke `storage/app/public`.

    Untuk mengakses file, pengguna harus melewati route aplikasi yang sesuai, yaitu:
    - `/submissions/{submission}/download`
    - `/materials/{material}/download`
    - `/materials/{material}/file`
  
    Sebelum mengirimkan file, controller memeriksa hak akses pengguna melalui Policy. Pada submission, pemeriksaan dilakukan menggunakan `Gate::authorize('view', $submission)`. Mahasiswa hanya dapat mengakses submission miliknya sendiri, sedangkan dosen pengampu dan admin juga dapat mengakses sesuai kewenangannya.

    Pada fitur materi, controller juga memeriksa hak akses melalui `MaterialPolicy`. Pengujian repository membuktikan bahwa mahasiswa yang tidak terdaftar ditolak dengan respons 403.

    Namun, repository memiliki mekanisme tambahan untuk data lama, seperti membaca file dari disk public dan menyediakan file dummy untuk beberapa data seed. Oleh karena itu, hasil akses terhadap data lama dapat berbeda dari file yang baru diunggah.

4. Perbedaan disk local, private, dan public terletak pada lokasi penyimpanan serta cara file diakses.
    | Disk | Lokasi penyimpanan | Penggunaan |
    |:---:|:---:|:---:|
    | local | storage/app/private | Menyimpan materi dan submission yang aksesnya dibatasi |
    | private | storage/app/private | Disk alternatif untuk penyimpanan privat |
    | public | storage/app/public | Menyimpan file yang memang boleh diakses secara publik |

    Disk local dan private menggunakan direktori yang sama, yaitu `storage/app/private`. Sementara itu, disk public dapat diakses melalui URL `/storage` jika symbolic link sudah dibuat.

    Pada repository KampusLMS, upload materi dan submission baru menggunakan disk local. Dengan demikian, pengguna harus mengaksesnya melalui controller yang memeriksa hak akses, bukan melalui URL publik secara langsung.

5. Perintah php artisan `storage:link` membuat symbolic link yang menghubungkan direktori `public/storage` dengan `storage/app/public`.
    Hubungannya adalah:
    ```
    public/storage -> storage/app/public
    ```
    Dengan adanya symbolic link tersebut, file yang tersimpan di `storage/app/public` dapat diakses melalui URL `/storage/....`

    Materi kuliah dan submission tugas tidak boleh disimpan di direktori tersebut karena file berpotensi diakses langsung oleh siapa pun yang mengetahui URL-nya, tanpa harus login atau melewati pemeriksaan hak akses. Oleh karena itu, file yang bersifat privat harus disimpan di `storage/app/private` dan dikirim melalui controller yang memeriksa Policy.

## BREAK

| # | Yang dicoba | Yang harus Anda amati | Prediksi | Yang saya pelajari |
|---|---|---|---|---|
| 1 | Pindahkan berkas submission ke `storage/app/public`, akses URL-nya tanpa login | Tugas seluruh kelas terbuka untuk publik | Berkas dapat diunduh langsung via URL http://localhost/storage/submissions/nama_file.pdf tanpa melewati middleware autentikasi | Berkas sensitif (materi/submission) pantang disimpan di disk public karena mengabaikan semua otorisasi server dan dapat diakses siapa saja yang tahu URL-nya |
| 2 | Hapus `Gate::authorize()` dari endpoint download, akses submission orang lain | IDOR versi berkas | Pengguna dapat mengunduh berkas submission milik orang lain hanya dengan mengganti ID submission pada URL/request | Keberadaan file privat di disk local tidak berguna jika endpoint download-nya tidak diproteksi oleh Policy `(Gate::authorize())` |
| 3 | Simpan berkas dengan `storeAs()` memakai nama asli, lalu dua akun mahasiswa berbeda mengunggah `tugas.pdf` dengan isi berbeda ke tugas yang sama | Berkas mahasiswa pertama tertimpa | Berkas milik mahasiswa pertama akan tertimpa secara permanen oleh berkas mahasiswa kedua yang mengunggah berkas dengan nama yang sama | Nama berkas dari pengguna adalah untrusted input. Berkas fisik wajib disimpan dengan nama acak dari Laravel (`store()`) dan nama asli disimpannya di database |
| 4 | Unggah berkas PHP yang diganti namanya jadi `.pdf` dengan validasi `extensions`, lalu dengan `mimes` | Beda memeriksa ekstensi vs isi | File yang hanya diganti nama ekstensinya menjadi .pdf lebih mungkin lolos validasi | Aturan `extensions` lolos karena hanya melihat teks nama `.pdf`, sedangkan `mimes` menolak request karena membaca magic bytes asli berkas PHP |
| 5 | Ubah `max:5120` menjadi `max:5`, unggah berkas 1 MB | Satuannya kilobyte | File berukuran lebih dari sekitar 5 KB akan ditolak | Parameter max pada validasi berkas Laravel menggunakan satuan Kilobyte (KB), bukan Megabyte (MB). max:5 berarti 5 KB, sedangkan untuk 5 MB harus max:5120 |
| 6 | Set `upload_max_filesize=1M` di php.ini, unggah berkas 3 MB dengan validasi `max:5120` | Batas server mengalahkan validasi | PHP dapat menolak upload sebelum Laravel memvalidasinya | Batas upload PHP dan post_max_size harus sesuai dengan kebutuhan aplikasi |
| 7 | Hapus `updateOrCreate`, ganti `create`, kumpulkan tugas dua kali | Unique constraint minggu 3 menyelamatkan Anda | Database tetap mencegah data ganda, tetapi file baru berpotensi tertinggal jika penyimpanan record gagal | `updateOrCreate()` mendukung pengumpulan ulang, sedangkan unique constraint mencegah duplikasi record |
| 8 | Hapus satu submission dari database lewat SQL langsung, cek folder storage | Berkas yatim tertinggal | Baris di database hilang, tetapi file fisik tetap tersimpan di folder `storage/app/private/` |Event model `deleting/deleted` hanya terpicu jika penghapusan dilakukan lewat Eloquent. Penghapusan via `SQL/cascadeOnDelete` membutuhkan pembersihan berkas fisik secara eksplisit |

## Chechkpoint Minggu 9

- [ ] Kenapa submission tidak boleh disimpan di `storage/app/public`? Peragakan kebocorannya.
- [ ] Kenapa nama berkas fisik dibuat acak? Serangan apa yang dicegah?
- [ ] Apa beda validasi `mimes` dan `extensions`? Mana yang lebih aman dan kenapa?
- [ ] `max:5120` berarti berapa MB? Apa yang bisa membatalkan batas ini di level server?
- [ ] Tunjukkan di kode Anda apa yang mencegah mahasiswa mengunduh submission mahasiswa lain.
- [ ] Kenapa `is_late` dihitung di server, bukan dikirim dari browser?