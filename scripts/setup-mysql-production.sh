#!/usr/bin/env bash
# ==============================================================================
# Skrip Otomatisasi Konfigurasi Database MySQL Produksi
# Penanggung Jawab: Nabil Hafidz Mubarok (Infrastruktur, Database & Supervisor)
# ==============================================================================
set -euo pipefail

DB_NAME="kampuslms_prod"
DB_USER="kampuslms_user"
DB_HOST="127.0.0.1"
DB_PORT="3306"

echo "======================================================="
echo " [Nabil] Inisialisasi Database MySQL Produksi: $DB_NAME "
echo "======================================================="

# Generate password acak yang kuat (24 karakter alfanumerik)
if command -v openssl >/dev/null 2>&1; then
    DB_PASSWORD=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9!@#$%^&*()_+=-' | head -c 24)
else
    DB_PASSWORD=$(tr -dc 'a-zA-Z0-9!@#$%^&*()_+=-' </dev/urandom | head -c 24)
fi

echo "[1/4] Membuat database dan user khusus di MySQL..."
sudo mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED WITH caching_sha2_password BY '${DB_PASSWORD}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED WITH caching_sha2_password BY '${DB_PASSWORD}';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, REFERENCES, LOCK TABLES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "[2/4] Menguji koneksi user baru ke database '${DB_NAME}'..."
mysql -u"${DB_USER}" -p"${DB_PASSWORD}" -h localhost -e "SELECT DATABASE() as 'Koneksi Berhasil:';" "${DB_NAME}"

echo "[3/4] Konfigurasi snippet untuk berkas .env produksi:"
echo "-------------------------------------------------------"
cat <<ENV_OUTPUT
DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}
ENV_OUTPUT
echo "-------------------------------------------------------"

echo "[4/4] Setup database produksi selesai dengan aman."

