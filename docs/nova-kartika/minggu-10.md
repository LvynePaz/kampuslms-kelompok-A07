## READ
1. Matikan worker. Buat satu tugas baru. Buka tabel jobs — apa isinya?
   Jawaban: saat workernya dimatikan dan satu tugas baru dipublikasi, tabel `jobs` berisi satu job yang menunggu untuk diproses. dari hasil tinker job tsb memiliki id 1, menggunakan antrean `default`, dan kolom `attempts` bernilai 0. kolom `reserved_at` juga bernilai `null`, berarti job belum diambil oleh worker. di kolom `payload`, terdapat informasi tentang listener yang akan dijalankan dan tugas yang dibawa sama event

2. Baca kolom payload. Bisakah Anda mengenali nama Listener dan ID tugasnya?
   Jawaban: bisa, di kolom `payload` nama listener bisa dikenali dari bagian `displayName`, yaitu `App\Listeners\SendAssignmentPublishedNotification`. lalu id tugas bisa dikenali di bagian objek `Assignment`, yaitu id 19. kolom `commandName` berisi `Illuminate\Events\CallQueuedListener`, yang merupakan class laravel untuk menangani listener yang masuk ke queue. jadi payload menyimpan informasi yang diperlukan laravel untuk menjalankan listener dan mengetahui tugas yang dibawa sama event

3. Jalankan php artisan queue:work, amati keluarannya. Periksa tabel jobs lagi.
   Jawaban: setelah jalanin perintah `php artisan queue:work`, listener `SendAssignmentPublishedNotification` berhasil dijalankan dengan status `DONE`. setelah itu ada 10 job `NewAssignmentNotification` yang juga berhasil diproses dengan status `DONE`. ketika tabel jobs diperiksa lagi menggunakan tinker, hasilnya kosong `(all: [])`. berarti menunjukkan bahwa job yang tadi menunggu di antrean sudah selesai diproses dan tidak lagi tersimpan di tabel jobs

4. Periksa tabel notifications. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
   Jawaban: dari hasil tinker, tabel `notifications` berisi 10 baris notifikasi. setelah diperiksa mata kuliah dengan id 6 memiliki 10 mahasiswa yang terdaftar, sedangkan jumlah notifikasi untuk tugas dengan id 19 juga ada 10. setiap notifikasi memiliki `notifiable_id` yang beda beda, sehingga notifikasi dikirim ke 10 pengguna yang berbeda. jadi jumlah notifikasi sesuai dengan jumlah mahasiswa yang terdaftar di mata kuliah tersebut

5. Baca kolom data pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?
   Jawaban: dari hasil tinker, kolom data di salah satu notifikasi berisi:
   ```php
    {
        "assignment_id": 19,
        "title": "tugas 1 tes"
    }
    ```

    setelah diperiksa menggunakan `array_keys()`, cuma ada dua data, yaitu `assignment_id` dan `title`. tidak ada informasi tambahan seperti nilai atau feedback dosen yang ikut tersimpan. jadi isi kolom data sudah sesuai sama kebutuhan notifikasi karena hanya menyimpan id dan judul tugas, tanpa ada informasi lain yang tidak diperlukan

## BREAK

| **#** | **Yang dicoba** | **Yang harus Anda amati** | **Apa yang terjadi** | **Yang dipelajari** |
|---|---|---|---|---|
| 1 | Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | **Gagal total tanpa satu pun pesan error** | tugas berhasil dibuat, tapi notifikasi belum diproses. ada 1 job di tabel `jobs` dan belum ada notifikasi di tabel `notifications`. | kalau worker belum dijalankan, job hanya menunggu di queue dan notifikasi belum terkirim. |
| 2 | Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Rasakan sendiri lamanya | listener berjalan langsung, tapi notifikasi masih masuk ke queue karena notification masih memakai `ShouldQueue`. ada 40 job di tabel `jobs`. | menghapus `ShouldQueue` dari listener belum tentu membuat semua proses berjalan langsung kalau notificationnya masih memakai `ShouldQueue`. |
| 3 | Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Bandingkan dengan nomor 1 dan 2 | notifikasi langsung diproses tanpa menunggu worker. ada 40 notifikasi di tabel `notifications` dan tidak ada job tersisa di tabel `jobs`. | `sync` membuat job langsung diproses. prosesnya bisa lebih lama karena notifikasi ikut dikirim saat tugas dibuat. |
| 4 | Buat Notification melempar exception, jalankan worker | Amati `tries` dan tabel `failed_jobs` | job notifikasi gagal setelah dicoba 3 kali dan masuk ke tabel `failed_jobs`. notifikasi tidak berhasil disimpan. | `tries` mengatur berapa kali job dicoba. kalau tetap gagal, job dicatat di `failed_jobs` supaya penyebabnya bisa diperiksa. |
| 5 | Hentikan worker di tengah pemrosesan job | Job kembali ke queue atau hilang? | job tidak langsung hilang saat worker dihentikan. setelah waktu `retry_after` lewat, worker dijalankan lagi dan job berhasil diproses. | job yang belum selesai bisa dicoba lagi setelah waktu tunggunya habis, jadi job tidak selalu hilang saat worker berhenti. |
| 6 | Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Kebocoran data permanen | nilai `87.50` tersimpan di payload notifikasi mahasiswa. tapi pengujian tidak menunjukkan nilai tersebut masuk ke inbox admin. | data yang dimasukkan ke payload bisa tersimpan di database. jadi, informasi sensitif seperti nilai mahasiswa harus dibatasi dan hanya disimpan kalau memang diperlukan. |
| 7 | Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain | IDOR versi notifikasi | saat pengguna A mencoba menandai notifikasi milik pengguna B, hasilnya `404 Not Found`. notifikasi pengguna B tetap belum dibaca. | endpoint harus mengecek apakah notifikasi milik pengguna yang sedang login supaya pengguna lain tidak bisa mengubahnya. |
| 8 | Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Penyadapan realtime | pengujian tidak dilakukan karena proyek belum memakai reverb. konfigurasi broadcast masih `log`, jadi belum ada hasil pengujian penyadapan secara langsung. | channel publik bisa diakses lebih luas daripada private channel. data pribadi sebaiknya memakai private channel dengan pengecekan akses. |