-- ==============================================================================
-- Skrip Setup Database MySQL Produksi KampusLMS
-- Proyek: KampusLMS (Kelompok A07)
-- Penanggung Jawab: Nabil Hafidz Mubarok (Infrastruktur, Database & Supervisor)
-- Database: kampuslms_prod
-- User: kampuslms_user@localhost
-- ==============================================================================

-- 1. Buat Database Produksi dengan Karakter UTF-8 Lengkap (Emoji & Simbol)
CREATE DATABASE IF NOT EXISTS `kampuslms_prod`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Buat Pengguna Khusus Aplikasi (Hanya Terikat ke localhost)
-- CATATAN KEAMANAN:
-- Ganti 'K4mpusLMS#Pr0d_9x8v2qW1z@2026!' dengan string password acak yang dihasilkan via:
-- $ openssl rand -base64 24
CREATE USER IF NOT EXISTS 'kampuslms_user'@'localhost' 
  IDENTIFIED WITH caching_sha2_password BY 'K4mpusLMS#Pr0d_9x8v2qW1z@2026!';

-- 3. Batasi Hak Akses (Prinsip Least Privilege)
-- User HANYA diberi izin DDL dan DML pada database `kampuslms_prod` saja.
-- User TIDAK diberikan hak administratif global (*.*), SHUTDOWN, SUPER, atau GRANT OPTION.
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, REFERENCES, LOCK TABLES
  ON `kampuslms_prod`.*
  TO 'kampuslms_user'@'localhost';

-- 4. Terapkan Pembaruan Hak Akses
FLUSH PRIVILEGES;

-- 5. Verifikasi Hak Akses User
SHOW GRANTS FOR 'kampuslms_user'@'localhost';

