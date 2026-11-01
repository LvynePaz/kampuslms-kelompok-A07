<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Durasi cache statistik global: 60 menit (3600 detik).
     */
    private const GLOBAL_CACHE_TTL = 3600;

    /**
     * Durasi cache statistik personal pengguna: 10 menit (600 detik).
     */
    private const USER_CACHE_TTL = 600;

    /**
     * Tampilkan halaman dashboard utama dengan implementasi Cache::remember.
     * Butir BUILD 6 Minggu 11: Optimasi query dashboard & strategi invalidasi cache.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Cache statistik global: Total Mata Kuliah
        $courseCount = Cache::remember('dashboard.course_count', self::GLOBAL_CACHE_TTL, function () {
            return Course::count();
        });

        // 2. Cache statistik global: Total Pengguna
        $userCount = Cache::remember('dashboard.user_count', self::GLOBAL_CACHE_TTL, function () {
            return User::count();
        });

        // 3. Cache statistik per pengguna sesuai perannya (Dosen / Mahasiswa)
        $myCourseCount = $this->getMyCourseCount($user, $courseCount);

        return view('dashboard', compact('courseCount', 'userCount', 'myCourseCount'));
    }

    /**
     * Alias invoke method untuk fleksibilitas routing.
     */
    public function __invoke(Request $request): View
    {
        return $this->index($request);
    }

    /**
     * Mengambil jumlah mata kuliah personal dengan caching per user ID.
     */
    private function getMyCourseCount(?User $user, int $defaultCount): int
    {
        if (! $user) {
            return $defaultCount;
        }

        $cacheKey = match ($user->role) {
            'dosen'     => "dashboard.user_{$user->id}_taught_courses_count",
            'mahasiswa' => "dashboard.user_{$user->id}_enrolled_courses_count",
            default     => null,
        };

        if (! $cacheKey) {
            return $defaultCount;
        }

        return Cache::remember($cacheKey, self::USER_CACHE_TTL, function () use ($user, $defaultCount) {
            return match ($user->role) {
                'dosen'     => $user->taughtCourses()->count(),
                'mahasiswa' => $user->courses()->count(),
                default     => $defaultCount,
            };
        });
    }

    /**
     * Strategi Invalidasi Cache (BUILD 6):
     * Membersihkan cache statistik global saat data mata kuliah atau user bertambah/berkurang.
     */
    public static function invalidateGlobalStats(): void
    {
        Cache::forget('dashboard.course_count');
        Cache::forget('dashboard.user_count');
    }

    /**
     * Membersihkan cache statistik spesifik satu pengguna saat terjadi enrollment atau penugasan.
     */
    public static function invalidateUserStats(User $user): void
    {
        Cache::forget("dashboard.user_{$user->id}_taught_courses_count");
        Cache::forget("dashboard.user_{$user->id}_enrolled_courses_count");
    }
}

