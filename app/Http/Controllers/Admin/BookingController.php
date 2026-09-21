<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->toString();

        $bookings = Booking::query()
            ->with(['room', 'user'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhereHas('room', fn ($room) => $room->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statuses' => BookingStatus::cases(),
            'filters' => compact('status', 'search'),
        ]);
    }

    public function approve(Booking $booking, BookingService $bookings): RedirectResponse
    {
        $bookings->approve($booking);

        return back()->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function reject(Booking $booking, BookingService $bookings): RedirectResponse
    {
        $bookings->reject($booking);

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }
}
