#!/usr/bin/env bash
# ==============================================================================
# Skrip Otomatisasi Instalasi & Konfigurasi Supervisor di VPS
# Penanggung Jawab: Nabil Hafidz Mubarok (Infrastruktur, Database & Supervisor)
# ==============================================================================
set -euo pipefail

SOURCE_CONF="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/deploy/supervisor/kampuslms-worker.conf"
TARGET_CONF="/etc/supervisor/conf.d/kampuslms-worker.conf"
LOG_FILE="/var/log/supervisor/kampuslms-worker.log"

echo "======================================================="
echo " [Nabil] Setup Supervisor Queue Worker - KampusLMS     "
echo "======================================================="

# 1. Pastikan Supervisor terinstal di sistem
if ! command -v supervisord >/dev/null 2>&1; then
    echo "[1/5] Supervisor belum terinstal. Menginstal via apt..."
    sudo apt-get update -y
    sudo apt-get install -y supervisor
else
    echo "[1/5] Supervisor sudah terinstal."
fi

# 2. Siapkan file log dengan izin yang benar untuk www-data
echo "[2/5] Menyiapkan berkas log di ${LOG_FILE}..."
sudo touch "${LOG_FILE}"
sudo chown www-data:www-data "${LOG_FILE}"
sudo chmod 664 "${LOG_FILE}"

# 3. Salin berkas konfigurasi ke /etc/supervisor/conf.d/
echo "[3/5] Menyalin konfigurasi worker ke ${TARGET_CONF}..."
if [ ! -f "${SOURCE_CONF}" ]; then
    echo "ERROR: Berkas konfigurasi ${SOURCE_CONF} tidak ditemukan!" >&2
    exit 1
fi
sudo cp "${SOURCE_CONF}" "${TARGET_CONF}"

# 4. Muat ulang konfigurasi Supervisor
echo "[4/5] Memperbarui daemon Supervisor (reread & update)..."
sudo supervisorctl reread
sudo supervisorctl update

# 5. Jalankan atau restart worker jika sudah berjalan
echo "[5/5] Memastikan proses worker berjalan..."
sudo supervisorctl restart kampuslms-worker:* || sudo supervisorctl start kampuslms-worker:*

echo ""
echo "======================================================="
echo " STATUS SUPERVISOR WORKER:                            "
echo "======================================================="
sudo supervisorctl status kampuslms-worker:*
echo "======================================================="
echo " Worker berhasil disiapkan untuk memproses antrean 24/7! "

