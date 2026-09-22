<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\SawDecisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SawDecisionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_saw_ranks_and_applies_server_side_recommendation_atomically(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = $this->room();
        $highPriority = $this->booking($room, $admin, [
            'title' => 'Rapat Direksi',
            'activity_type' => 'direksi_eksternal',
            'attendees_count' => 24,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);
        $lowPriority = $this->booking($room, User::factory()->create(), [
            'title' => 'Rapat Rutin',
            'activity_type' => 'rutin',
            'attendees_count' => 3,
            'start_time' => '09:30',
            'end_time' => '11:30',
        ]);

        $groups = app(SawDecisionService::class)->conflictAnalyses();

        $this->assertCount(1, $groups);
        $this->assertSame($highPriority->id, $groups[0]['analysis']['winner']['booking']->id);

        $this->actingAs($admin)
            ->post(route('admin.conflicts.apply', $lowPriority, absolute: false))
            ->assertSessionHasNoErrors();

        $this->assertSame(BookingStatus::Confirmed, $highPriority->refresh()->status);
        $this->assertSame(BookingStatus::Cancelled, $lowPriority->refresh()->status);
    }

    public function test_non_admin_cannot_access_conflict_analysis(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.conflicts.index', absolute: false))
            ->assertForbidden();
    }

    private function room(): Room
    {
        return Room::query()->create([
            'code' => 'R-SAW',
            'name' => 'Ruang SAW',
            'capacity' => 30,
            'location' => 'Lantai 1',
            'floor' => '1',
            'status' => 'available',
        ]);
    }

    private function booking(Room $room, User $user, array $overrides): Booking
    {
        return Booking::query()->create(array_merge([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_dept' => 'Operasional',
            'room_id' => $room->id,
            'title' => 'Agenda',
            'date' => today()->addDays(5)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'activity_type' => 'internal_divisi',
            'attendees_count' => 5,
            'status' => BookingStatus::Pending,
        ], $overrides));
    }
}
