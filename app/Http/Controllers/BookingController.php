<?php

namespace App\Http\Controllers;

use App\Data\BookingData;
use App\Enums\ActivityType;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with('room')
            ->where('user_id', $request->user()->id)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('id')
            ->paginate(12);

        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request): View
    {
        $requestedDate = $request->string('date')->toString();
        $selectedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)
            && $requestedDate >= today()->format('Y-m-d')
            ? $requestedDate
            : today()->format('Y-m-d');

        return view('bookings.create', [
            'rooms' => Room::query()->where('status', '!=', 'maintenance')->orderBy('code')->get(),
            'activityTypes' => ActivityType::cases(),
            'selectedRoomId' => (int) $request->query('room_id'),
            'selectedDate' => $selectedDate,
        ]);
    }

    public function store(StoreBookingRequest $request, BookingService $bookings): RedirectResponse
    {
        $booking = $bookings->create($request->user(), BookingData::fromValidated($request->validated()));
        $message = $booking->status->value === 'confirmed'
            ? 'Pemesanan berhasil dibuat dan langsung dikonfirmasi.'
            : 'Pengajuan berhasil disimpan dan menunggu persetujuan admin.';

        return redirect()->route('bookings.index')->with('success', $message);
    }

    public function cancel(Request $request, Booking $booking, BookingService $bookings): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $bookings->cancel($booking);

        return back()->with('success', 'Pemesanan berhasil dibatalkan.');
    }
}
