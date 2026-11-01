<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    public function test_dashboard_uses_cache_remember_and_reduces_queries(): void
    {
        Cache::flush();

        $dosen = User::where('role', 'dosen')->first();
        if (! $dosen) {
            $dosen = User::factory()->create(['role' => 'dosen']);
        }

        $this->actingAs($dosen);

        // Hit 1: Cold Cache (menghitung dari DB dan menyimpan ke Cache)
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response1 = $this->get('/dashboard');
        $response1->assertStatus(200);

        $coldQueries = count(DB::getQueryLog());
        $this->assertTrue(Cache::has('dashboard.course_count'));
        $this->assertTrue(Cache::has('dashboard.user_count'));
        $this->assertTrue(Cache::has("dashboard.user_{$dosen->id}_taught_courses_count"));

        // Hit 2: Warm Cache (mengambil dari Cache tanpa mengeksekusi agregat count lagi)
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response2 = $this->get('/dashboard');
        $response2->assertStatus(200);

        $warmQueries = count(DB::getQueryLog());

        // Hit kedua harus memiliki jumlah query lebih sedikit dari hit pertama
        $this->assertLessThan($coldQueries, $warmQueries);
    }

    public function test_dashboard_cache_invalidation(): void
    {
        Cache::put('dashboard.course_count', 999, 3600);
        Cache::put('dashboard.user_count', 888, 3600);

        $this->assertTrue(Cache::has('dashboard.course_count'));
        $this->assertTrue(Cache::has('dashboard.user_count'));

        DashboardController::invalidateGlobalStats();

        $this->assertFalse(Cache::has('dashboard.course_count'));
        $this->assertFalse(Cache::has('dashboard.user_count'));
    }
}

