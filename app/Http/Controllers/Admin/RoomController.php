<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\Room;
use App\Services\RoomAdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        return view('admin.rooms.index', [
            'rooms' => Room::query()->withCount(['bookings', 'displays'])->orderBy('code')->paginate(12),
            'statuses' => RoomStatus::cases(),
        ]);
    }

    public function store(StoreRoomRequest $request, RoomAdministrationService $rooms): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $rooms->create($data, $request->file('image'));

        return back()->with('success', 'Ruangan baru berhasil ditambahkan.');
    }

    public function update(UpdateRoomRequest $request, Room $room, RoomAdministrationService $rooms): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $rooms->update($room, $data, $request->file('image'));

        return back()->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room, RoomAdministrationService $rooms): RedirectResponse
    {
        $rooms->delete($room);

        return back()->with('success', 'Ruangan tanpa riwayat berhasil dihapus.');
    }
}
