# Panduan Catatan Mingguan & Alur Kerja Git — Kelompok A07

Repositori ini menyimpan seluruh catatan aktivitas mingguan, laporan eksplorasi (*Read–Break–Fix–Build*), serta persiapan interview untuk proyek **KampusLMS (Laravel 12)** mata kuliah Pemrograman Web.

---


## Panduan Perintah Git Biar Aman Intinya

### 1.Alur Rutin Mengisi Catatan & Update ke GitHub
Jalankan langkah ini setiap kali selesai mengisi catatan mingguan:

```bash
# 1. Selalu tarik update terbaru dari remote sebelum mulai kerjain apapun itu 
git pull origin main

# 2. Buka dan edit file mingguan di folder masing-masing
# Contoh: docs/patra_61/minggu-01.md

# 3. Simpan perubahan ke Git
git add .
git commit -m "isi catatan minggu 1 oleh [nama]"

# 4. push ke repo kita ke branch masing-masing atau main
git push origin <nama_branch_kamu>
```

---

### 2. Ini Cara kalau kalian ada error pas mau push/pull sama hapus file atau foler 
Jika ada file lama/salah yang ingin dihapus dari repositori:

```bash
# Hapus file dari git & disk
git rm "nama_file_yang_mau_dihapus.md"

# Atau jika file sudah terlanjur dihapus manual di VS Code, cukup lakukan:
git add -A

# Simpan & push perubahannya
git commit -m "hapus file lama yang sudah tidak dipakai"
git push
```

---

### 3. cara ambil perubahan branch anggota 
Jika nabil membuat halaman login pakai react trus branchnya nabil_55 dan ingin masukin ke branch main:

```bash
# Tarik 1 baris langsung:
git pull origin nabil_55
```

---

## Standar Format Catatan Tiap Minggu (Berdasarkan Modul)
Setiap minggu mengikuti 4 tahapan praktikum:
1. **READ**: Hasil eksplorasi kode, penelusuran alur request, pembedahan arsitektur Laravel 12.
2. **BREAK**: Tabel eksperimen kerusakan sengaja (prediksi vs fakta error yang didapat).
3. **FIX**: Analisis bug dan perbaikan branch latihan modul.
4. **BUILD**: Catatan progres implementasi fitur proyek kelompok mingguan.
5. **CHECKPOINT**: Jawaban evaluasi mandiri & penguasaan poin wawancara/interview dosen.
