#!/usr/bin/env bash
# ==============================================================================
# Skrip Pengujian Pemrosesan Antrean Notifikasi saat Sesi SSH Ditutup
# Penanggung Jawab: Nabil Hafidz Mubarok (Infrastruktur, Database & Supervisor)
# ==============================================================================
set -euo pipefail

APP_DIR="/var/www/kampuslms"
LOG_FILE="/var/log/supervisor/kampuslms-worker.log"

echo "======================================================================"
echo " [Nabil] Uji Pemrosesan Antrean Latar Belakang & Ketahanan Sesi SSH   "
echo "======================================================================"

cd "${APP_DIR}"

# 1. Verifikasi Status Supervisor Worker
echo "[1/4] Memeriksa status proses worker di bawah Supervisor..."
sudo supervisorctl status kampuslms-worker:*

# Periksa Parent Process ID (PPID) untuk membuktikan independensi dari shell SSH
echo ""
echo ">> Verifikasi Process Tree (Membuktikan Worker Anak dari Supervisord, BUKAN SSH PTS):"
ps -ef | grep "[q]ueue:work" || true

# 2. Ambil Jumlah Notifikasi Sebelum Event
echo ""
echo "[2/4] Menghitung total notifikasi sebelum tugas baru dibuat..."
NOTIF_BEFORE=$(php artisan tinker --execute="echo App\Models\Notification::count();")
echo "Jumlah notifikasi awal: ${NOTIF_BEFORE}"

# 3. Simulasi Pembuatan Tugas & Trigger Event AssignmentPublished
echo ""
echo "[3/4] Melakukan dispatch event AssignmentPublished via Artisan..."
php artisan tinker --execute="
\$course = App\Models\Course::with('students')->first();
if (\$course && \$course->students->count() > 0) {
    \$assignment = App\Models\Assignment::create([
        'course_id' => \$course->id,
        'title' => 'Tugas Uji Supervisor Nonstop ' . now()->format('H:i:s'),
        'description' => 'Tugas ini dibuat untuk membuktikan queue worker tetap berjalan mandiri saat sesi SSH terputus.',
        'due_date' => now()->addDays(7),
        'status' => 'published',
        'points_possible' => 100,
    ]);
    event(new App\Events\AssignmentPublished(\$assignment));
    echo 'Event AssignmentPublished berhasil di-dispatch untuk ID Assignment: ' . \$assignment->id . PHP_EOL;
} else {
    echo 'Data kelas atau mahasiswa belum tersedia di database.' . PHP_EOL;
}
"

# 4. Verifikasi Pemrosesan oleh Background Worker
echo ""
echo "[4/4] Memeriksa hasil pemrosesan antrean oleh Worker..."
sleep 4

NOTIF_AFTER=$(php artisan tinker --execute="echo App\Models\Notification::count();")
JOBS_REMAINING=$(php artisan tinker --execute="echo DB::table('jobs')->count();")

echo "Jumlah notifikasi sekarang : ${NOTIF_AFTER}"
echo "Sisa antrean di tabel jobs : ${JOBS_REMAINING} (Harus 0 jika sukses diproses)"

echo ""
echo ">> Potongan Log Terbaru dari ${LOG_FILE}:"
echo "----------------------------------------------------------------------"
sudo tail -n 10 "${LOG_FILE}" || true
echo "----------------------------------------------------------------------"

echo ""
echo "[PENJELASAN KETAHANAN SESI SSH]"
echo "Saat Anda menutup jendela terminal SSH (logout):"
echo "1. Kernel Linux mengirimkan sinyal SIGHUP ke proses yang berada dalam grup sesi PTS tersebut."
echo "2. Karena worker di-spawn oleh daemon 'supervisord' (yang berjalan di bawah systemd / PID 1),"
echo "   maka PPID worker adalah supervisord, BUKAN sesi SSH Anda."
echo "3. Oleh sebab itu, worker TIDAK menerima SIGHUP dan tetap memproses notifikasi 24/7!"
echo "======================================================================"

