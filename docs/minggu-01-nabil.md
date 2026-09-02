**Check Point Minggu 1**
1. Browser → Route → Controller → Model → Database → Model → Controller → View → HTML → Browser
2. Karena folder public/ memang dirancang sebagai satu-satunya pintu masuk aplikasi dari internet.
3. ```.env``` berisi data rahasia seperti password dan API key, jadi tidak boleh di-commit. ```.env.example``` hanya berisi contoh konfigurasi, jadi boleh di-commit agar anggota tim tahu konfigurasi apa yang diperlukan.
4. Karena banyak tutorial yang dibuat untuk Laravel versi lama, ketika middleware masih didaftarkan melalui ```app/Http/Kernel.php.``` Mulai Laravel 11 dan tetap di Laravel 12, struktur aplikasinya berubah sehingga konfigurasi middleware dipindahkan ke ```bootstrap/app.php.```
5. ```APP_DEBUG=true``` di server produksi berisiko karena dapat membocorkan informasi penting aplikasi saat terjadi error. Sebaiknya gunakan: ```APP_DEBUG=false```, Ingat: ```true``` untuk development, ```false``` untuk production.
6. 