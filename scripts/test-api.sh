#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
STUDENT_EMAIL="${STUDENT_EMAIL:?Set STUDENT_EMAIL to a seeded mahasiswa account}"
STUDENT_PASSWORD="${STUDENT_PASSWORD:?Set STUDENT_PASSWORD}"

unauth_status="$(curl -sS -o /dev/null -w '%{http_code}' \
  -H 'Accept: application/json' "$BASE_URL/api/v1/me")"
if [[ "$unauth_status" != "401" ]]; then
  echo "Expected unauthenticated /me to return 401; received $unauth_status" >&2
  exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
  echo "jq is required to prepare login credentials and read the token" >&2
  exit 1
fi

credentials="$(jq -n --arg email "$STUDENT_EMAIL" --arg password "$STUDENT_PASSWORD" \
  '{email: $email, password: $password}')"
login_response="$(curl -sS -f -X POST "$BASE_URL/api/v1/auth/login" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d "$credentials")"

token="$(printf '%s' "$login_response" | jq -er '.data.token')"

forbidden_status="$(curl -sS -o /dev/null -w '%{http_code}' \
  -X POST "$BASE_URL/api/v1/assignments" \
  -H 'Accept: application/json' -H "Authorization: Bearer $token" \
  -H 'Content-Type: application/json' -d '{}')"
if [[ "$forbidden_status" != "403" ]]; then
  echo "Expected mahasiswa assignment creation to return 403; received $forbidden_status" >&2
  exit 1
fi

curl -sS -f -X POST "$BASE_URL/api/v1/auth/logout" \
  -H 'Accept: application/json' -H "Authorization: Bearer $token" >/dev/null

revoked_status="$(curl -sS -o /dev/null -w '%{http_code}' \
  -H 'Accept: application/json' -H "Authorization: Bearer $token" \
  "$BASE_URL/api/v1/me")"
if [[ "$revoked_status" != "401" ]]; then
  echo "Expected revoked token to return 401; received $revoked_status" >&2
  exit 1
fi

echo "API authorization checks passed."
