<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_open_approval_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.bookings.index', absolute: false))
            ->assertForbidden();
    }

    public function test_admin_can_approve_pending_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $booking = $this->createPendingBooking();

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $booking, absolute: false))
            ->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(AttendanceStatus::Scheduled, $booking->attendance_status);
    }

    public function test_admin_cannot_approve_booking_that_conflicts_with_confirmed_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = $this->createPendingBooking();
        Booking::query()->create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'user_dept' => 'Admin',
            'room_id' => $pending->room_id,
            'title' => 'Jadwal Terkonfirmasi',
            'date' => $pending->date->format('Y-m-d'),
            'start_time' => '09:30',
            'end_time' => '10:30',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 3,
            'status' => BookingStatus::Confirmed,
            'attendance_status' => AttendanceStatus::Scheduled,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $pending, absolute: false))
            ->assertSessionHasErrors('booking');

        $this->assertSame(BookingStatus::Pending, $pending->refresh()->status);
    }

    private function createPendingBooking(): Booking
    {
        $user = User::factory()->create();
        $room = Room::query()->create([
            'code' => 'R-ADM',
            'name' => 'Ruang Approval',
            'capacity' => 12,
            'location' => 'Lantai 2',
            'floor' => 'Lantai 2',
            'status' => 'available',
        ]);

        return Booking::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_dept' => 'Keuangan',
            'room_id' => $room->id,
            'title' => 'Pengajuan Approval',
            'date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 5,
            'status' => BookingStatus::Pending,
        ]);
    }
}
