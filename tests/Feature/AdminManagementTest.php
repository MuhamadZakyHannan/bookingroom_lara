<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomDisplay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_room_and_room_with_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.rooms.store', absolute: false), [
            'code' => 'r-new',
            'name' => 'Ruang Baru',
            'capacity' => 10,
            'location' => 'Gedung A',
            'floor' => '2',
            'status' => 'available',
        ])->assertSessionHasNoErrors();

        $room = Room::query()->where('code', 'R-NEW')->firstOrFail();
        $this->booking($room, $admin);

        $this->actingAs($admin)
            ->delete(route('admin.rooms.destroy', $room, absolute: false))
            ->assertSessionHasErrors('room');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_admin_cannot_change_own_role_or_delete_user_with_booking_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->booking($this->room(), $user);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin, absolute: false), ['role' => 'user'])
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user, absolute: false))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_can_register_display_and_regenerate_its_token(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = $this->room();

        $this->actingAs($admin)->post(route('admin.displays.store', absolute: false), [
            'room_id' => $room->id,
            'display_name' => 'Monitor Pintu',
        ])->assertSessionHasNoErrors();

        $display = RoomDisplay::query()->firstOrFail();
        $oldToken = $display->display_token;

        $this->actingAs($admin)
            ->post(route('admin.displays.regenerate', $display, absolute: false))
            ->assertSessionHasNoErrors();

        $this->assertNotSame($oldToken, $display->refresh()->display_token);
        $this->assertStringStartsWith('DISP-', $display->display_token);
    }

    private function room(): Room
    {
        return Room::query()->create([
            'code' => 'R-'.strtoupper(fake()->unique()->lexify('???')),
            'name' => 'Ruang '.fake()->unique()->word(),
            'capacity' => 20,
            'location' => 'Gedung A',
            'floor' => '1',
            'status' => 'available',
        ]);
    }

    private function booking(Room $room, User $user): Booking
    {
        return Booking::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_dept' => 'Internal',
            'room_id' => $room->id,
            'title' => 'Riwayat Booking',
            'date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 4,
            'status' => BookingStatus::Pending,
        ]);
    }
}
