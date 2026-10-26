# Catatan Individu Minggu 10 — Nabil Hafidz (`nabil_55`)

**Topik:** Pemrosesan Latar Belakang (Queue) & In-App Notification  
**Fokus Tugas Kelompok (Nomor 3 / Butir 4 & 5):** Lonceng Navbar, Kotak Masuk (Inbox) Notifikasi, Polling Berkala JavaScript, & Proteksi Anti-IDOR.

---

## 1. Implementasi Komponen & File Terkait

| Komponen / Fitur | File Target | Deskripsi Pekerjaan |
|---|---|---|
| **Notification Controller** | `app/Http/Controllers/NotificationController.php` | - `index()`: Menampilkan inbox notifikasi terpaginasi.<br>- `unreadCount()`: Endpoint JSON untuk polling jumlah notifikasi belum dibaca.<br>- `markAsRead()`: Menandai notifikasi telah dibaca secara **Anti-IDOR**.<br>- `markAllAsRead()`: Menandai seluruh notifikasi user sebagai telah dibaca. |
| **Routing Web** | `routes/web.php` | Mendaftarkan 4 endpoint notifikasi di dalam grup middleware `auth`: `/notifications`, `/notifications/unread-count`, `/notifications/{id}/read`, dan `/notifications/read-all`. |
| **Lonceng & Badge Navbar** | `resources/views/components/layout.blade.php` | Memasang ikon lonceng SVG interaktif dan badge merah dinamis berisi jumlah notifikasi belum dibaca (`unread_count`). |
| **Polling JavaScript** | `resources/views/components/layout.blade.php` | Menjalankan interval `setInterval(checkUnreadNotifications, 30000)` setiap 30 detik tanpa me-refresh halaman untuk menyinkronkan badge notifikasi di navbar. |
| **Tampilan Inbox** | `resources/views/notifications/index.blade.php` | Antarmuka kotak masuk notifikasi bertema Sage Harmony dengan indikator kartu *unread* vs *read*, tombol "Buka Detail", "Tandai Dibaca", dan state kotak masuk kosong (*empty state*). |
| **Automated Testing** | `tests/Feature/NotificationFeatureTest.php` | Memverifikasi seluruh alur notifikasi, otorisasi tamu (guest redirect), polling unread count, dan pengujian ketat keamanan **Anti-IDOR**. |

---

## 2. Penjelasan Teknis & Proteksi Anti-IDOR

### Mengapa `$request->user()->notifications()->findOrFail($id)` Kebal IDOR?
- Jika pencarian notifikasi dilakukan langsung melalui Model global `Notification::findOrFail($id)` atau `DB::table('notifications')->where('id', $id)`, penyerang dapat mengganti `$id` via URL/POST untuk menandai atau memanipulasi notifikasi milik mahasiswa lain (*Insecure Direct Object Reference*).
- Dengan memanggil method relasi `$request->user()->notifications()`, query SQL yang dieksekusi Laravel secara otomatis menyertakan klausa `WHERE notifiable_type = 'App\Models\User' AND notifiable_id = ?`.
- Ketika Pengguna A mencoba memasukkan ID notifikasi milik Pengguna B, query mengembalikan 0 baris sehingga `findOrFail()` secara otomatis memicu `ModelNotFoundException` dan menghasilkan HTTP **404 Not Found**.

---

## 3. Hasil Pengujian Otomatis (Feature Test)

Perintah pengujian yang dijalankan:
```bash
php artisan test --filter=NotificationFeatureTest
```

Hasil:
- `✓ guest cannot access notifications and is redirected to login`
- `✓ authenticated user can view notifications inbox`
- `✓ unread count polling endpoint returns json count`
- `✓ user can mark notification as read`
- `✓ user can mark all notifications as read`
- `✓ idor protection user cannot mark other users notification`
- `✓ navbar renders bell icon and unread badge for authenticated user`

**Status:** 7 passed (22 assertions), seluruh 78 test aplikasi lolos 100%.
