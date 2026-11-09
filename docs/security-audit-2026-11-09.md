# Security Hardening dan Audit Anti-Kebocoran

## Konteks deployment

Deployment produksi saat ini menggunakan Railway dengan Apache dan SSL/TLS
otomatis pada Railway Edge. Berkas
[`deploy/nginx/kampuslms.conf.example`](../deploy/nginx/kampuslms.conf.example)
adalah referensi untuk deployment VPS terpisah; Railway tidak membaca konfigurasi
Nginx tersebut.

Untuk konfigurasi environment produksi di Railway, gunakan:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

Nilai tersebut belum diterapkan ke `.env` lokal atau repository.

## Referensi pemasangan SSL VPS

```bash
sudo apt install nginx certbot python3-certbot-nginx
sudo certbot --nginx -d kampuslms.example.com
sudo certbot renew --dry-run
```

## Bukti audit `curl -I`

Audit dijalankan pada 9 November 2026 terhadap deployment produksi:

```text
curl -I https://kampuslms-production-a07.up.railway.app/.env
HTTP/1.1 404 Not Found

curl -I https://kampuslms-production-a07.up.railway.app/storage/logs/laravel.log
HTTP/1.1 404 Not Found

curl -I https://kampuslms-production-a07.up.railway.app/composer.json
HTTP/1.1 404 Not Found

curl -I http://kampuslms-production-a07.up.railway.app/
HTTP/1.1 301 Moved Permanently
location: https://kampuslms-production-a07.up.railway.app/
```

Status `404` membuktikan file sensitif tidak tersedia melalui web. Redirect
`301` membuktikan permintaan HTTP diarahkan ke HTTPS.

## Poin wawancara

- `APP_DEBUG=true` dapat membocorkan stack trace, path file, konfigurasi, dan
  detail koneksi aplikasi.
- Document Root yang mengarah ke root proyek dapat mengekspos `.env`,
  `composer.json`, source code, dan direktori `vendor`.
- Document Root harus mengarah ke direktori `public/`, sehingga hanya aset dan
  entry point web yang tersedia.
