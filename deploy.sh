#!/usr/bin/env bash
# ==============================================================================
# Skrip Deployment Otomatis KampusLMS — Kelompok A07
# Penulis / Pengelola: Patra Ananda (10241061)
# Deskripsi: Menjalankan pembaruan kode produksi dari git pull hingga siap pakai.
# ==============================================================================

# 1. set -e: Menghentikan eksekusi skrip seketika jika ada satu saja perintah yang gagal.
#    AKIBAT JIKA DIHILANGKAN: Jika git pull atau composer gagal di tengah jalan, skrip
#    akan nekat menjalankan migrasi atau menyalakan web dalam kondisi kode setengah jadi/korup.
set -e

echo "--------------------------------------------------------"
echo "🚀 [$(date '+%Y-%m-%d %H:%M:%S')] Memulai Deployment KampusLMS..."
echo "--------------------------------------------------------"

# 2. php artisan down: Memasang mode pemeliharaan (maintenance mode).
#    AKIBAT JIKA DIHILANGKAN: Pengguna aktif bisa mengalami crash Error 500 saat
#    file PHP sedang ditimpa atau migrasi tabel database sedang berjalan.
echo "📦 Mengaktifkan Maintenance Mode..."
php artisan down --retry=60 --secret="a07-bypass-maintenance-key" || true

# 3. git pull: Mengambil pembaruan kode terbaru dari branch utama (main).
#    AKIBAT JIKA DIHILANGKAN: Server tidak akan pernah memperbarui kodenya
#    dan akan terus menjalankan versi aplikasi yang lama.
echo "📥 Menarik kode terbaru dari Git (origin/main)..."
git pull origin main

# 4. composer install: Memperbarui paket dependensi PHP khusus produksi.
#    --no-dev: Mencegah paket dev (Debugbar, PHPUnit, Faker) terpasang di server.
#    --optimize-autoloader: Merangkum peta class PHP ke dalam array memori cepat.
#    AKIBAT JIKA DIHILANGKAN: Fitur baru yang memakai package baru akan error 'Class not found',
#    dan Debugbar bisa bocor ke publik jika paket dev terpasang.
echo "📚 Menginstal dependensi Composer (Production)..."
composer install --no-dev --optimize-autoloader --no-interaction

# 5. npm ci && npm run build: Mengompilasi ulang aset CSS Tailwind dan JavaScript Vite.
#    AKIBAT JIKA DIHILANGKAN: Tampilan web menjadi rusak (CSS berantakan / tidak ada styling)
#    atau memicu error 'Vite manifest not found'.
echo "🎨 Mengompilasi aset frontend (Vite & Tailwind CSS)..."
npm ci --silent
npm run build

# 6. php artisan migrate --force: Menjalankan migrasi struktur tabel ke database produksi.
#    --force: Wajib disertakan karena Laravel secara default menolak migrasi di environment production.
#    AKIBAT JIKA DIHILANGKAN: Database gagal membuat tabel/kolom baru sehingga fitur baru gagal query SQL.
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

# 7. Empat Sekawan Cache: Membekukan konfigurasi, rute, template Blade, dan event ke file statis.
#    AKIBAT JIKA DIHILANGKAN: Web merespons lambat karena server harus membaca ulang
#    puluhan file konfigurasi dan view di hard disk pada setiap request pengguna.
echo "⚡ Membuat cache statis untuk performa tinggi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. php artisan queue:restart: Meminta queue worker Laravel memuat ulang kode baru di RAM.
#    AKIBAT JIKA DIHILANGKAN: Worker (Supervisor) adalah long-lived process di RAM; jika tidak direstart,
#    worker akan terus mengeksekusi logika kode versi lama dan mengabaikan kode baru hasil deploy.
echo "🔄 Merestart Queue Worker (Supervisor)..."
php artisan queue:restart

# 9. Health Check Mandiri: Memeriksa apakah aplikasi merespons HTTP sebelum mode maintenance dibuka.
#    AKIBAT JIKA DIHILANGKAN: Pengembang tidak tahu bahwa aplikasi sebenarnya gagal boot/500,
#    sehingga pengguna langsung disambut layar error publik.
echo "🩺 Melakukan Health Check internal server..."
HEALTH_CHECK_URL="http://127.0.0.1/login"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_CHECK_URL" || true)

if [ "$HTTP_STATUS" -eq 200 ] || [ "$HTTP_STATUS" -eq 302 ]; then
    echo "✅ Health check berhasil! Respon HTTP: $HTTP_STATUS."
    
    # 10. php artisan up: Membuka kembali aplikasi untuk dapat diakses oleh publik.
    echo "🌐 Menonaktifkan Maintenance Mode (Aplikasi Online)..."
    php artisan up
    echo "--------------------------------------------------------"
    echo "🎉 [$(date '+%Y-%m-%d %H:%M:%S')] Deployment KampusLMS Berhasil Sempurna!"
    echo "--------------------------------------------------------"
else
    echo "❌ KRITIS: Health check gagal (Respon HTTP: $HTTP_STATUS)! Terjadi error pada aplikasi."
    echo "⚠️ Sistem TETAP DITAHAN dalam mode maintenance demi mencegah bocornya error ke publik."
    echo "Silakan periksa log aplikasi dengan perintah: tail -n 50 storage/logs/laravel.log"
    exit 1
fi

