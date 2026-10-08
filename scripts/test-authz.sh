#!/usr/bin/env bash
# ==============================================================================
# scripts/test-authz.sh — Skrip Pengujian Otorisasi & Pencegahan IDOR
# Kelompok A07 — Pemrograman Web (Laravel 12)
# ==============================================================================
set -euo pipefail

BASE_URL="${BASE_URL:-http://kampuslms-kelompok-a07.test}"
STUDENT_EMAIL="${STUDENT_EMAIL:-mahasiswa@kampuslms.test}"
STUDENT_PASSWORD="${STUDENT_PASSWORD:-password}"
DOSEN_EMAIL="${DOSEN_EMAIL:-dosen@kampuslms.test}"
DOSEN_PASSWORD="${DOSEN_PASSWORD:-password}"

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "========================================================"
echo "  UJI OTORISASI & PENCEGAHAN IDOR — KAMPUSLMS A07"
echo "  Target URL: $BASE_URL"
echo "========================================================"

# ------------------------------------------------------------------------------
# 1. Uji Guest: Memastikan endpoint privat terkunci (HTTP 401)
# ------------------------------------------------------------------------------
echo -n "[1/5] Menguji proteksi endpoint privat tanpa token... "
status_guest=$(curl -s -o /dev/null -w "%{http_code}" -H "Accept: application/json" "$BASE_URL/api/v1/me" || true)

if [ "$status_guest" = "401" ]; then
    echo -e "${GREEN}PASS (401 Unauthorized)${NC}"
else
    echo -e "${RED}FAIL (Expected 401, got $status_guest)${NC}"
    exit 1
fi

# ------------------------------------------------------------------------------
# 2. Login Mahasiswa & Dapatkan Token Sanctum
# ------------------------------------------------------------------------------
echo -n "[2/5] Autentikasi akun Mahasiswa ($STUDENT_EMAIL)... "
login_mhs=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
    -H "Accept: application/json" \
    -d "email=$STUDENT_EMAIL" \
    -d "password=$STUDENT_PASSWORD")

token_mhs=$(echo "$login_mhs" | grep -o '"token":"[^"]*' | cut -d'"' -f4 || true)

if [ -n "$token_mhs" ]; then
    echo -e "${GREEN}BERHASIL${NC}"
else
    echo -e "${RED}GAGAL MENDAPATKAN TOKEN${NC}"
    echo "Respons: $login_mhs"
    exit 1
fi

# ------------------------------------------------------------------------------
# 3. Uji IDOR & Otorisasi: Mahasiswa DILARANG Membuat Tugas (Harus 403)
# ------------------------------------------------------------------------------
echo -n "[3/5] Menguji mahasiswa mencoba membuat tugas (POST /assignments)... "
status_mhs_create=$(curl -s -o /dev/null -w "%{http_code}" \
    -X POST "$BASE_URL/api/v1/assignments" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $token_mhs" \
    -d "title=Tugas+Ilegal" \
    -d "due_at=2026-12-31" || true)

if [ "$status_mhs_create" = "403" ]; then
    echo -e "${GREEN}PASS (403 Forbidden — Terlindungi Policy)${NC}"
else
    echo -e "${RED}FAIL (Expected 403, got $status_mhs_create)${NC}"
    exit 1
fi

# ------------------------------------------------------------------------------
# 4. Login Dosen & Dapatkan Token Sanctum
# ------------------------------------------------------------------------------
echo -n "[4/5] Autentikasi akun Dosen ($DOSEN_EMAIL)... "
login_dosen=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
    -H "Accept: application/json" \
    -d "email=$DOSEN_EMAIL" \
    -d "password=$DOSEN_PASSWORD")

token_dosen=$(echo "$login_dosen" | grep -o '"token":"[^"]*' | cut -d'"' -f4 || true)

if [ -n "$token_dosen" ]; then
    echo -e "${GREEN}BERHASIL${NC}"
else
    echo -e "${RED}GAGAL MENDAPATKAN TOKEN${NC}"
    echo "Respons: $login_dosen"
    exit 1
fi

# ------------------------------------------------------------------------------
# 5. Uji Akses Sah: Dosen mengakses daftar mata kuliah (Harus 200)
# ------------------------------------------------------------------------------
echo -n "[5/5] Menguji akses sah Dosen ke katalog mata kuliah... "
status_dosen_courses=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $token_dosen" \
    -H "Cache-Control: no-cache" \
    "$BASE_URL/api/v1/courses" || true)

if [ "$status_dosen_courses" = "200" ]; then
    echo -e "${GREEN}PASS (200 OK)${NC}"
else
    echo -e "${RED}FAIL (Expected 200, got $status_dosen_courses)${NC}"
    exit 1
fi

# Cleanup: Revoke token
curl -s -X POST "$BASE_URL/api/v1/auth/logout" -H "Authorization: Bearer $token_mhs" > /dev/null 2>&1 || true
curl -s -X POST "$BASE_URL/api/v1/auth/logout" -H "Authorization: Bearer $token_dosen" > /dev/null 2>&1 || true

echo "========================================================"
echo -e "${GREEN}SELURUH PENGUJIAN OTORISASI & KEAMANAN LOLOS (100%)${NC}"
echo "========================================================"
