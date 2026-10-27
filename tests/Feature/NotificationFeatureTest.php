<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        $this->userB = User::factory()->create([
            'role' => 'mahasiswa',
        ]);
    }

    /**
     * Helper untuk membuat database notification Laravel secara manual.
     */
    private function createNotification(User $user, array $data = [], bool $isRead = false): string
    {
        $id = (string) Str::uuid();

        $user->notifications()->create([
            'id'      => $id,
            'type'    => 'App\Notifications\NewAssignmentNotification',
            'data'    => array_merge([
                'title'       => 'Tugas Baru: Proyek M3',
                'message'     => 'Dosen mengunggah tugas baru pada Pemrograman Web.',
                'course_name' => 'Pemrograman Web',
                'url'         => '/courses/1/assignments/1',
            ], $data),
            'read_at' => $isRead ? now() : null,
        ]);

        return $id;
    }

    public function test_guest_cannot_access_notifications_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('notifications.index'));
        $response->assertRedirect(route('login'));

        $pollingResponse = $this->getJson(route('notifications.unread_count'));
        $pollingResponse->assertUnauthorized();
    }

    public function test_authenticated_user_can_view_notifications_inbox(): void
    {
        $this->createNotification($this->userA, ['title' => 'Tugas 1 Web'], false);
        $this->createNotification($this->userA, ['title' => 'Nilai UTS Keluar'], true);

        $response = $this->actingAs($this->userA)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Kotak Masuk Notifikasi');
        $response->assertSee('Tugas 1 Web');
        $response->assertSee('Nilai UTS Keluar');
        $response->assertSee('1 belum dibaca');
    }

    public function test_unread_count_polling_endpoint_returns_json_count(): void
    {
        $this->createNotification($this->userA, [], false);
        $this->createNotification($this->userA, [], false);
        $this->createNotification($this->userA, [], true); // Sudah dibaca

        $response = $this->actingAs($this->userA)
            ->getJson(route('notifications.unread_count'));

        $response->assertOk();
        $response->assertJson([
            'unread_count' => 2,
        ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $notifId = $this->createNotification($this->userA, [], false);

        $response = $this->actingAs($this->userA)
            ->post(route('notifications.read', $notifId));

        $response->assertSessionHas('success');
        $this->assertNotNull($this->userA->notifications()->find($notifId)->read_at);
        $this->assertEquals(0, $this->userA->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->createNotification($this->userA, [], false);
        $this->createNotification($this->userA, [], false);
        $this->assertEquals(2, $this->userA->unreadNotifications()->count());

        $response = $this->actingAs($this->userA)
            ->post(route('notifications.read_all'));

        $response->assertSessionHas('success');
        $this->assertEquals(0, $this->userA->fresh()->unreadNotifications()->count());
    }

    /**
     * Uji Proteksi IDOR (Insecure Direct Object Reference):
     * Pengguna A dilarang menandai dibaca notifikasi milik Pengguna B.
     * Sistem wajib merespons 404 (Not Found) karena pencarian melalui $request->user()->notifications()->findOrFail($id).
     */
    public function test_idor_protection_user_cannot_mark_other_users_notification(): void
    {
        $userBNotifId = $this->createNotification($this->userB, [
            'title' => 'Notifikasi Rahasia User B',
        ], false);

        // User A mencoba menandai notifikasi milik User B
        $response = $this->actingAs($this->userA)
            ->post(route('notifications.read', $userBNotifId));

        // Wajib 404 (ModelNotFoundException dari user->notifications()->findOrFail())
        $response->assertNotFound();

        // Pastikan notifikasi User B tetap utuh dan belum dibaca
        $this->assertNull($this->userB->notifications()->find($userBNotifId)->read_at);
    }

    public function test_navbar_renders_bell_icon_and_unread_badge_for_authenticated_user(): void
    {
        $this->createNotification($this->userA, [], false);
        $this->createNotification($this->userA, [], false);

        $response = $this->actingAs($this->userA)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('nav-notif-bell');
        $response->assertSee('nav-notif-badge');
        $response->assertSee('2');
    }
}
