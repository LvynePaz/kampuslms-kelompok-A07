<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Butir 4 & 5 BUILD Minggu 10:
     * Tampilkan daftar kotak masuk notifikasi pengguna yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ambil notifikasi pengguna terbaru dengan paginasi
        $notifications = $user->notifications()
            ->latest()
            ->paginate(10);

        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Endpoint polling JavaScript untuk mengecek jumlah notifikasi belum dibaca.
     * Dipanggil setiap 30 detik tanpa me-refresh halaman.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Tandai sebuah notifikasi telah dibaca.
     * 
     * Anti-IDOR:
     * Pencarian dilakukan ketat melalui relasi $request->user()->notifications()->findOrFail($id).
     * Jika pengguna mencoba mengakses ID notifikasi milik orang lain,
     * Laravel akan otomatis menghasilkan respons 404 (ModelNotFoundException).
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai telah dibaca.',
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        // Jika terdapat parameter redirect (misal user mengklik link dari item notifikasi)
        if ($redirect = $request->input('redirect')) {
            return redirect($redirect);
        }

        return back()->with('success', 'Notifikasi berhasil ditandai telah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi milik pengguna saat ini sebagai telah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil ditandai telah dibaca.',
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'Semua notifikasi berhasil ditandai telah dibaca.');
    }
}
