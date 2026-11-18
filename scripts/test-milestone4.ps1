# ==============================================================================
# Skrip Pengujian Otomatis Hasil Build Minggu 12 (Milestone M4)
# Proyek: KampusLMS — Kelompok A07
# Cara Menjalankan:
#   powershell -ExecutionPolicy Bypass -File .\scripts\test-milestone4.ps1
# ==============================================================================

param(
    [string]$TargetUrl = "https://kampuslms-kelompok-a07-production.up.railway.app"
)

Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "   PENGUJIAN HASIL BUILD MINGGU 12 (MILESTONE M4) — KAMPUSLMS   " -ForegroundColor Cyan
Write-Host "   Target Server: $TargetUrl" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

$PassedCount = 0
$FailedCount = 0

function Assert-Check {
    param(
        [string]$TestName,
        [bool]$Condition,
        [string]$Expected,
        [string]$Actual
    )

    if ($Condition) {
        Write-Host " [PASS] " -ForegroundColor Green -NoNewline
        Write-Host "$TestName (Hasil: $Actual)" -ForegroundColor White
        $script:PassedCount++
    } else {
        Write-Host " [FAIL] " -ForegroundColor Red -NoNewline
        Write-Host "$TestName (Harapan: $Expected, Hasil: $Actual)" -ForegroundColor Yellow
        $script:FailedCount++
    }
}

# ------------------------------------------------------------------------------
# BAGIAN 1: PENGUJIAN ANTI-DISKUALIFIKASI & KEAMANAN DOCUMENT ROOT
# ------------------------------------------------------------------------------
Write-Host "--- 1. UJI ANTI-DISKUALIFIKASI & DOCUMENT ROOT ---" -ForegroundColor Magenta

# 1.1 Uji akses file .env (Wajib 404)
try {
    $resEnv = Invoke-WebRequest -Uri "$TargetUrl/.env" -Method Head -UseBasicParsing -ErrorAction Stop
    $codeEnv = $resEnv.StatusCode
} catch {
    $codeEnv = $_.Exception.Response.StatusCode.value__
}
Assert-Check -TestName "Proteksi File .env (Anti-Diskualifikasi #1)" -Condition ($codeEnv -eq 404) -Expected "404" -Actual "$codeEnv"

# 1.2 Uji akses composer.json di luar folder public (Wajib 404)
try {
    $resComp = Invoke-WebRequest -Uri "$TargetUrl/composer.json" -Method Head -UseBasicParsing -ErrorAction Stop
    $codeComp = $resComp.StatusCode
} catch {
    $codeComp = $_.Exception.Response.StatusCode.value__
}
Assert-Check -TestName "Proteksi Berkas composer.json di luar public/" -Condition ($codeComp -eq 404) -Expected "404" -Actual "$codeComp"

# 1.3 Uji akses log aplikasi laravel.log (Wajib 404)
try {
    $resLog = Invoke-WebRequest -Uri "$TargetUrl/storage/logs/laravel.log" -Method Head -UseBasicParsing -ErrorAction Stop
    $codeLog = $resLog.StatusCode
} catch {
    $codeLog = $_.Exception.Response.StatusCode.value__
}
Assert-Check -TestName "Proteksi File Log Internal (storage/logs/laravel.log)" -Condition ($codeLog -eq 404) -Expected "404" -Actual "$codeLog"

# 1.4 Uji proteksi submission privat tanpa login (Wajib Redirect ke Login / Ditolak)
try {
    $resSub = Invoke-WebRequest -Uri "$TargetUrl/submissions/1/download" -Method Head -MaximumRedirection 0 -UseBasicParsing -ErrorAction Stop
    $codeSub = $resSub.StatusCode
} catch {
    $codeSub = $_.Exception.Response.StatusCode.value__
}
$isSubSecured = ($codeSub -eq 302 -or $codeSub -eq 401 -or $codeSub -eq 403 -or $codeSub -eq 404)
Assert-Check -TestName "Proteksi Unduh Submission Privat Tanpa Login (Anti-Diskualifikasi #2)" -Condition $isSubSecured -Expected "302/401/403/404" -Actual "$codeSub"

# 1.5 Uji Protokol HTTPS
$isHttps = $TargetUrl.StartsWith("https://")
Assert-Check -TestName "Aplikasi Berjalan di Atas Protokol Aman HTTPS" -Condition $isHttps -Expected "HTTPS" -Actual $(if ($isHttps) { "HTTPS" } else { "HTTP" })

Write-Host ""

# ------------------------------------------------------------------------------
# BAGIAN 2: PENGUJIAN FUNGSIONALITAS WEB & MODE DEBUG
# ------------------------------------------------------------------------------
Write-Host "--- 2. UJI FUNGSIONALITAS WEB & APP_DEBUG ---" -ForegroundColor Magenta

# 2.1 Halaman Login Publik
try {
    $resLogin = Invoke-WebRequest -Uri "$TargetUrl/login" -Method Get -UseBasicParsing -ErrorAction Stop
    $codeLogin = $resLogin.StatusCode
} catch {
    $codeLogin = $_.Exception.Response.StatusCode.value__
}
Assert-Check -TestName "Halaman Login Publik Dapat Diakses" -Condition ($codeLogin -eq 200) -Expected "200" -Actual "$codeLogin"

# 2.2 Uji Error 404 Tidak Membocorkan Debug Ignition (Anti-Diskualifikasi #3)
try {
    $resErr = Invoke-WebRequest -Uri "$TargetUrl/url-sengaja-salah-untuk-uji-debug-12345" -Method Get -UseBasicParsing -ErrorAction Stop
    $contentErr = $resErr.Content
    $codeErr = $resErr.StatusCode
} catch {
    $codeErr = $_.Exception.Response.StatusCode.value__
    $contentErr = ""
}
$hasIgnitionLeak = $contentErr.Contains("Ignition") -or $contentErr.Contains("APP_KEY")
Assert-Check -TestName "APP_DEBUG=false (Tidak Ada Bocoran Layar Ignition)" -Condition (-not $hasIgnitionLeak) -Expected "Tidak Bocor" -Actual $(if ($hasIgnitionLeak) { "BOCOR" } else { "Aman (Non-Debug)" })

Write-Host ""

# ------------------------------------------------------------------------------
# BAGIAN 3: PENGUJIAN AKUN DEMO & OTORISASI ROLE (API SANCTUM)
# ------------------------------------------------------------------------------
Write-Host "--- 3. UJI AKUN DEMO & OTORISASI ROLE (API V1) ---" -ForegroundColor Magenta

# 3.1 Login Akun Demo Admin
try {
    $bodyAdmin = @{ email = "admin@kampuslms.test"; password = "password" } | ConvertTo-Json
    $resAdmin = Invoke-RestMethod -Uri "$TargetUrl/api/v1/auth/login" -Method Post -Body $bodyAdmin -ContentType "application/json" -ErrorAction Stop
    $tokenAdmin = $resAdmin.token
} catch {
    $tokenAdmin = $null
}
Assert-Check -TestName "Akun Demo Admin (admin@kampuslms.test) Berfungsi" -Condition (-not [string]::IsNullOrEmpty($tokenAdmin)) -Expected "Token Sanctum Terbit" -Actual $(if ($tokenAdmin) { "Token Terbit" } else { "Gagal Login" })

# 3.2 Login Akun Demo Dosen
try {
    $bodyDosen = @{ email = "dosen@kampuslms.test"; password = "password" } | ConvertTo-Json
    $resDosen = Invoke-RestMethod -Uri "$TargetUrl/api/v1/auth/login" -Method Post -Body $bodyDosen -ContentType "application/json" -ErrorAction Stop
    $tokenDosen = $resDosen.token
} catch {
    $tokenDosen = $null
}
Assert-Check -TestName "Akun Demo Dosen (dosen@kampuslms.test) Berfungsi" -Condition (-not [string]::IsNullOrEmpty($tokenDosen)) -Expected "Token Sanctum Terbit" -Actual $(if ($tokenDosen) { "Token Terbit" } else { "Gagal Login" })

# 3.3 Login Akun Demo Mahasiswa
try {
    $bodyMhs = @{ email = "mahasiswa@kampuslms.test"; password = "password" } | ConvertTo-Json
    $resMhs = Invoke-RestMethod -Uri "$TargetUrl/api/v1/auth/login" -Method Post -Body $bodyMhs -ContentType "application/json" -ErrorAction Stop
    $tokenMhs = $resMhs.token
} catch {
    $tokenMhs = $null
}
Assert-Check -TestName "Akun Demo Mahasiswa (mahasiswa@kampuslms.test) Berfungsi" -Condition (-not [string]::IsNullOrEmpty($tokenMhs)) -Expected "Token Sanctum Terbit" -Actual $(if ($tokenMhs) { "Token Terbit" } else { "Gagal Login" })

# 3.4 Uji Otorisasi: Mahasiswa Dilarang Membuat Tugas (Wajib 403 Forbidden)
if ($tokenMhs) {
    try {
        $headersMhs = @{
            "Authorization" = "Bearer $tokenMhs"
            "Accept" = "application/json"
        }
        $resIdor = Invoke-WebRequest -Uri "$TargetUrl/api/v1/assignments" -Method Post -Headers $headersMhs -Body @{ title = "Uji Hack Mahasiswa" } -UseBasicParsing -ErrorAction Stop
        $codeIdor = $resIdor.StatusCode
    } catch {
        $codeIdor = $_.Exception.Response.StatusCode.value__
    }
    Assert-Check -TestName "Pencegahan IDOR (Mahasiswa Ditolak Membuat Tugas)" -Condition ($codeIdor -eq 403) -Expected "403 Forbidden" -Actual "$codeIdor"
} else {
    Assert-Check -TestName "Pencegahan IDOR (Mahasiswa Ditolak Membuat Tugas)" -Condition $false -Expected "403 Forbidden" -Actual "Token Mhs Tidak Tersedia"
}

Write-Host ""
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "HASIL PENGUJIAN: $PassedCount Lolos, $FailedCount Gagal" -ForegroundColor $(if ($FailedCount -eq 0) { "Green" } else { "Yellow" })

if ($FailedCount -eq 0) {
    Write-Host "STATUS AKHIR: SEMUA PERSYARATAN BUILD MINGGU 12 (M4) LOLOS 100%!" -ForegroundColor Green
} else {
    Write-Host "STATUS AKHIR: ADA PENGUJIAN YANG GAGAL. SILAKAN CEK DETAIL DI ATAS." -ForegroundColor Red
}
Write-Host "================================================================" -ForegroundColor Cyan

