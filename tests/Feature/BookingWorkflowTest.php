<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_booking_is_pending_and_notifies_admin(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $room = $this->createRoom();

        $this->actingAs($user)
            ->post(route('bookings.store', absolute: false), $this->payload($room))
            ->assertRedirect(route('bookings.index'));

        $booking = Booking::firstOrFail();
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertNull($booking->attendance_status);
        $this->assertDatabaseHas('notifications', [
            'recipient_user_id' => $admin->id,
            'booking_id' => $booking->id,
            'type' => 'booking_pending',
        ]);
    }

    public function test_admin_booking_without_conflict_is_confirmed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = $this->createRoom();

        $this->actingAs($admin)
            ->post(route('bookings.store', absolute: false), $this->payload($room))
            ->assertRedirect(route('bookings.index'));

        $booking = Booking::firstOrFail();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(AttendanceStatus::Scheduled, $booking->attendance_status);
    }

    public function test_overlapping_admin_booking_is_kept_pending(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = $this->createRoom();
        $date = today()->addDay()->format('Y-m-d');
        $this->createBooking($admin, $room, [
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => BookingStatus::Confirmed,
            'attendance_status' => AttendanceStatus::Scheduled,
        ]);

        $this->actingAs($admin)
            ->post(route('bookings.store', absolute: false), $this->payload($room, [
                'date' => $date,
                'start_time' => '09:30',
                'end_time' => '10:30',
            ]))
            ->assertRedirect(route('bookings.index'));

        $this->assertSame(BookingStatus::Pending, Booking::latest('id')->firstOrFail()->status);
    }

    public function test_user_cannot_cancel_another_users_booking(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $booking = $this->createBooking($owner, $this->createRoom());

        $this->actingAs($otherUser)
            ->post(route('bookings.cancel', $booking, absolute: false))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    private function createRoom(): Room
    {
        return Room::query()->create([
            'code' => 'R-TST',
            'name' => 'Ruang Test',
            'capacity' => 20,
            'location' => 'Lantai 1',
            'floor' => 'Lantai 1',
            'status' => 'available',
        ]);
    }

    private function payload(Room $room, array $overrides = []): array
    {
        return array_merge([
            'room_id' => $room->id,
            'user_name' => 'Pemesan Test',
            'user_dept' => 'Teknologi Informasi',
            'title' => 'Rapat Pengujian',
            'date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'purpose' => 'Memvalidasi alur booking.',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 5,
        ], $overrides);
    }

    private function createBooking(User $user, Room $room, array $overrides = []): Booking
    {
        return Booking::query()->create(array_merge([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_dept' => 'TI',
            'room_id' => $room->id,
            'title' => 'Booking Awal',
            'date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 5,
            'status' => BookingStatus::Pending,
            'attendance_status' => null,
        ], $overrides));
    }
}
