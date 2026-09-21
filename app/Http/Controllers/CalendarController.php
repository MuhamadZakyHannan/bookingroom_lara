<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    private const COLORS = ['#2563eb', '#7c3aed', '#db2777', '#ea580c', '#059669', '#0891b2'];

    public function index(Request $request): View
    {
        return view('calendar.index', [
            'rooms' => Room::query()->orderBy('code')->get(),
            'selectedRoomId' => (int) $request->query('room_id'),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $roomId = max(0, (int) $request->query('room_id'));
        $events = Booking::query()
            ->with(['room', 'user'])
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value])
            ->when($roomId > 0, fn ($query) => $query->where('room_id', $roomId))
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'title' => $booking->room->name.': '.$booking->title,
                'start' => $booking->date->format('Y-m-d').'T'.$booking->start_time,
                'end' => $booking->date->format('Y-m-d').'T'.$booking->end_time,
                'backgroundColor' => self::COLORS[($booking->room_id - 1) % count(self::COLORS)],
                'borderColor' => self::COLORS[($booking->room_id - 1) % count(self::COLORS)],
                'extendedProps' => [
                    'room' => $booking->room->name,
                    'organizer' => $booking->user_name ?: $booking->user->name,
                    'purpose' => $booking->purpose,
                    'attendees' => $booking->attendees_count,
                    'status' => $booking->status->label(),
                ],
            ]);

        return response()->json($events);
    }
}
