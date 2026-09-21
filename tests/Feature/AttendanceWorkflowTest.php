<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_check_in_and_check_out_inside_valid_window(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-21 09:50:00'));
        $user = User::factory()->create();
        $booking = $this->createConfirmedBooking($user);

        $this->actingAs($user)
            ->post(route('bookings.check-in', $booking, absolute: false))
            ->assertSessionHasNoErrors();
        $this->assertSame(AttendanceStatus::CheckedIn, $booking->refresh()->attendance_status);

        $this->travelTo(CarbonImmutable::parse('2026-09-21 10:30:00'));
        $this->actingAs($user)
            ->post(route('bookings.check-out', $booking, absolute: false))
            ->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertSame(AttendanceStatus::CheckedOut, $booking->attendance_status);
    }

    public function test_booking_becomes_no_show_after_grace_period(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-21 10:16:00'));
        $booking = $this->createConfirmedBooking(User::factory()->create());

        $this->artisan('attendance:process')->assertSuccessful();

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertSame(AttendanceStatus::NoShow, $booking->attendance_status);
        $this->assertSame('system', $booking->attendance_updated_by);
    }

    public function test_checked_in_booking_is_automatically_checked_out_after_end_time(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-21 11:01:00'));
        $booking = $this->createConfirmedBooking(User::factory()->create(), [
            'attendance_status' => AttendanceStatus::CheckedIn,
            'check_in_at' => '2026-09-21 09:50:00',
        ]);

        $this->artisan('attendance:process')->assertSuccessful();

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertSame(AttendanceStatus::CheckedOut, $booking->attendance_status);
        $this->assertSame('system', $booking->attendance_updated_by);
    }

    private function createConfirmedBooking(User $user, array $overrides = []): Booking
    {
        $room = Room::query()->create([
            'code' => 'R-ATT'.Room::count(),
            'name' => 'Ruang Attendance',
            'capacity' => 10,
            'location' => 'Lantai 1',
            'floor' => 'Lantai 1',
            'status' => 'available',
        ]);

        return Booking::query()->create(array_merge([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_dept' => 'Operasional',
            'room_id' => $room->id,
            'title' => 'Rapat Attendance',
            'date' => '2026-09-21',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 5,
            'status' => BookingStatus::Confirmed,
            'attendance_status' => AttendanceStatus::Scheduled,
        ], $overrides));
    }
}
