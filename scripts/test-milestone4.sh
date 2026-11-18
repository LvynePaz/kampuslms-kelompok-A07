#!/usr/bin/env bash
# ==============================================================================
# Skrip Pengujian Otomatis Hasil Build Minggu 12 (Milestone M4)
# Proyek: KampusLMS — Kelompok A07
# Cara Menjalankan:
#   bash ./scripts/test-milestone4.sh
# ==============================================================================
set -euo pipefail

TARGET_URL="${1:-https://kampuslms-kelompok-a07-production.up.railway.app}"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

PASSED=0
FAILED=0

echo -e "${CYAN}================================================================${NC}"
echo -e "${CYAN}   PENGUJIAN HASIL BUILD MINGGU 12 (MILESTONE M4) — KAMPUSLMS   ${NC}"
echo -e "${YELLOW}   Target Server: $TARGET_URL${NC}"
echo -e "${CYAN}================================================================${NC}"
echo ""

assert_check() {
    local test_name="$1"
    local expected="$2"
    local actual="$3"

    if [ "$expected" = "$actual" ] || [[ "$expected" == *"$actual"* ]]; then
        echo -e " ${GREEN}[PASS]${NC} $test_name (Hasil: $actual)"
        PASSED=$((PASSED + 1))
    else
        echo -e " ${RED}[FAIL]${NC} $test_name (Harapan: $expected, Hasil: $actual)"
        FAILED=$((FAILED + 1))
    fi
}

# ------------------------------------------------------------------------------
# BAGIAN 1: PENGUJIAN ANTI-DISKUALIFIKASI & KEAMANAN DOCUMENT ROOT
# ------------------------------------------------------------------------------
echo -e "${MAGENTA}--- 1. UJI ANTI-DISKUALIFIKASI & DOCUMENT ROOT ---${NC}"

# 1.1 Uji akses file .env (Wajib 404)
code_env=$(curl -s -o /dev/null -w "%{http_code}" "$TARGET_URL/.env" || true)
assert_check "Proteksi File .env (Anti-Diskualifikasi #1)" "404" "$code_env"

# 1.2 Uji akses composer.json (Wajib 404)
code_comp=$(curl -s -o /dev/null -w "%{http_code}" "$TARGET_URL/composer.json" || true)
assert_check "Proteksi Berkas composer.json di luar public/" "404" "$code_comp"

# 1.3 Uji akses log aplikasi laravel.log (Wajib 404)
code_log=$(curl -s -o /dev/null -w "%{http_code}" "$TARGET_URL/storage/logs/laravel.log" || true)
assert_check "Proteksi File Log Internal (storage/logs/laravel.log)" "404" "$code_log"

# 1.4 Uji unduh submission privat tanpa login (Wajib Redirect ke Login / Ditolak)
code_sub=$(curl -s -o /dev/null -w "%{http_code}" "$TARGET_URL/submissions/1/download" || true)
is_sub_secured="FAIL"
if [ "$code_sub" = "302" ] || [ "$code_sub" = "401" ] || [ "$code_sub" = "403" ] || [ "$code_sub" = "404" ]; then
    is_sub_secured="SECURED"
fi
assert_check "Proteksi Unduh Submission Privat Tanpa Login (Anti-Diskualifikasi #2)" "SECURED" "$is_sub_secured"

# 1.5 Uji Protokol HTTPS
is_https="HTTP"
if [[ "$TARGET_URL" == https://* ]]; then
    is_https="HTTPS"
fi
assert_check "Aplikasi Berjalan di Atas Protokol Aman HTTPS" "HTTPS" "$is_https"

echo ""

# ------------------------------------------------------------------------------
# BAGIAN 2: PENGUJIAN FUNGSIONALITAS WEB & MODE DEBUG
# ------------------------------------------------------------------------------
echo -e "${MAGENTA}--- 2. UJI FUNGSIONALITAS WEB & APP_DEBUG ---${NC}"

# 2.1 Halaman Login Publik
code_login=$(curl -s -o /dev/null -w "%{http_code}" "$TARGET_URL/login" || true)
assert_check "Halaman Login Publik Dapat Diakses" "200" "$code_login"

# 2.2 Uji Error 404 Tidak Membocorkan Debug Ignition (Anti-Diskualifikasi #3)
body_err=$(curl -s "$TARGET_URL/url-sengaja-salah-untuk-uji-debug-12345" || true)
has_ignition="Aman"
if echo "$body_err" | grep -qi "Ignition\|APP_KEY"; then
    has_ignition="BOCOR"
fi
assert_check "APP_DEBUG=false (Tidak Ada Bocoran Layar Ignition)" "Aman" "$has_ignition"

echo ""

# ------------------------------------------------------------------------------
# BAGIAN 3: PENGUJIAN AKUN DEMO & OTORISASI ROLE (API SANCTUM)
# ------------------------------------------------------------------------------
echo -e "${MAGENTA}--- 3. UJI AKUN DEMO & OTORISASI ROLE (API V1) ---${NC}"

# 3.1 Login Admin
resp_admin=$(curl -s -X POST "$TARGET_URL/api/v1/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@kampuslms.test","password":"password"}' || true)
token_admin=$(echo "$resp_admin" | grep -o '"token":"[^"]*' | cut -d'"' -f4 || true)
token_admin_status="Gagal"
if [ -n "$token_admin" ]; then token_admin_status="Token Terbit"; fi
assert_check "Akun Demo Admin (admin@kampuslms.test) Berfungsi" "Token Terbit" "$token_admin_status"

# 3.2 Login Dosen
resp_dosen=$(curl -s -X POST "$TARGET_URL/api/v1/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"dosen@kampuslms.test","password":"password"}' || true)
token_dosen=$(echo "$resp_dosen" | grep -o '"token":"[^"]*' | cut -d'"' -f4 || true)
token_dosen_status="Gagal"
if [ -n "$token_dosen" ]; then token_dosen_status="Token Terbit"; fi
assert_check "Akun Demo Dosen (dosen@kampuslms.test) Berfungsi" "Token Terbit" "$token_dosen_status"

# 3.3 Login Mahasiswa
resp_mhs=$(curl -s -X POST "$TARGET_URL/api/v1/auth/login" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -d '{"email":"mahasiswa@kampuslms.test","password":"password"}' || true)
token_mhs=$(echo "$resp_mhs" | grep -o '"token":"[^"]*' | cut -d'"' -f4 || true)
token_mhs_status="Gagal"
if [ -n "$token_mhs" ]; then token_mhs_status="Token Terbit"; fi
assert_check "Akun Demo Mahasiswa (mahasiswa@kampuslms.test) Berfungsi" "Token Terbit" "$token_mhs_status"

# 3.4 Uji IDOR: Mahasiswa Dilarang Buat Tugas
if [ -n "$token_mhs" ]; then
    code_idor=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$TARGET_URL/api/v1/assignments" \
        -H "Accept: application/json" \
        -H "Authorization: Bearer $token_mhs" \
        -d "title=Uji Hack" || true)
    assert_check "Pencegahan IDOR (Mahasiswa Ditolak Membuat Tugas)" "403" "$code_idor"
else
    assert_check "Pencegahan IDOR (Mahasiswa Ditolak Membuat Tugas)" "403" "Token Mahasiswa Gagal"
fi

echo ""
echo -e "${CYAN}================================================================${NC}"
echo -e "HASIL PENGUJIAN: ${GREEN}$PASSED Lolos${NC}, ${RED}$FAILED Gagal${NC}"

if [ "$FAILED" -eq 0 ]; then
    echo -e "${GREEN}STATUS AKHIR: SEMUA PERSYARATAN BUILD MINGGU 12 (M4) LOLOS 100%!${NC}"
else
    echo -e "${RED}STATUS AKHIR: ADA PENGUJIAN YANG GAGAL. SILAKAN PERIKSA DETAIL DI ATAS.${NC}"
fi
echo -e "${CYAN}================================================================${NC}"

