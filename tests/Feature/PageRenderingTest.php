<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_and_calendar_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        [$room, $booking] = $this->createSchedule($user);

        $this->actingAs($user)->get(route('bookings.create', absolute: false))->assertOk()->assertSee('Pesan Ruang Rapat');
        $this->actingAs($user)->get(route('bookings.index', absolute: false))->assertOk()->assertSee($booking->title);
        $this->actingAs($user)->get(route('calendar.index', absolute: false))->assertOk()->assertSee('Kalender Ruangan');
        $this->actingAs($user)
            ->getJson(route('calendar.events', ['room_id' => $room->id], false))
            ->assertOk()
            ->assertJsonFragment(['id' => $booking->id, 'room' => $room->name]);
    }

    public function test_admin_approval_page_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createSchedule($admin, BookingStatus::Pending, null);

        $this->actingAs($admin)
            ->get(route('admin.bookings.index', absolute: false))
            ->assertOk()
            ->assertSee('Persetujuan Peminjaman');
    }

    public function test_security_headers_are_added_to_web_responses(): void
    {
        $this->get(route('login', absolute: false))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    private function createSchedule(
        User $user,
        BookingStatus $status = BookingStatus::Confirmed,
        ?AttendanceStatus $attendance = AttendanceStatus::Scheduled,
    ): array {
        $room = Room::query()->create([
            'code' => 'R-PAGE',
            'name' => 'Ruang Render',
            'capacity' => 10,
            'location' => 'Lantai 3',
            'floor' => 'Lantai 3',
            'status' => 'available',
        ]);
        $booking = Booking::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_dept' => 'Umum',
            'room_id' => $room->id,
            'title' => 'Rapat Render',
            'date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 4,
            'status' => $status,
            'attendance_status' => $attendance,
        ]);

        return [$room, $booking];
    }
}
