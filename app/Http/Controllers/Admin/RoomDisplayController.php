<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomDisplayRequest;
use App\Models\Room;
use App\Models\RoomDisplay;
use App\Services\RoomDisplayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomDisplayController extends Controller
{
    public function index(): View
    {
        return view('admin.displays.index', [
            'displays' => RoomDisplay::query()->with('room')->orderBy('display_name')->paginate(20),
            'rooms' => Room::query()->whereDoesntHave('displays')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRoomDisplayRequest $request, RoomDisplayService $displays): RedirectResponse
    {
        $displays->create($request->integer('room_id'), $request->string('display_name')->trim()->toString());

        return back()->with('success', 'Perangkat display berhasil didaftarkan.');
    }

    public function regenerate(RoomDisplay $display, RoomDisplayService $displays): RedirectResponse
    {
        $displays->regenerateToken($display);

        return back()->with('success', 'Token display berhasil diganti. URL lama tidak berlaku lagi.');
    }

    public function destroy(RoomDisplay $display, RoomDisplayService $displays): RedirectResponse
    {
        $displays->delete($display);

        return back()->with('success', 'Perangkat display berhasil dihapus.');
    }
}
