<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomDisplay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $minimumCapacity = max(0, (int) $request->query('min_capacity'));

        $rooms = Room::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('facilities', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['available', 'occupied', 'maintenance'], true), fn ($query) => $query->where('status', $status))
            ->when($minimumCapacity > 0, fn ($query) => $query->where('capacity', '>=', $minimumCapacity))
            ->orderBy('code')
            ->get();

        $todayBookings = Booking::query()
            ->with(['room', 'user'])
            ->whereDate('date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get();

        $pendingBookings = collect();
        $displays = collect();
        if ($request->user()->isAdmin()) {
            $pendingBookings = Booking::query()
                ->with(['room', 'user'])
                ->where('status', 'pending')
                ->whereDate('date', '>=', today())
                ->orderBy('date')
                ->orderBy('start_time')
                ->limit(3)
                ->get();

            $displays = RoomDisplay::query()
                ->with('room')
                ->orderBy('display_name')
                ->get();
        }

        return view('dashboard', [
            'rooms' => $rooms,
            'todayBookings' => $todayBookings,
            'pendingBookings' => $pendingBookings,
            'displays' => $displays,
            'stats' => [
                'rooms' => Room::count(),
                'available' => Room::where('status', 'available')->count(),
                'today' => $todayBookings->count(),
                'users' => User::count(),
            ],
            'filters' => compact('search', 'status', 'minimumCapacity'),
        ]);
    }
}
