<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomDisplay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoomDisplayService
{
    public function create(int $roomId, string $name): RoomDisplay
    {
        return DB::transaction(function () use ($roomId, $name): RoomDisplay {
            Room::query()->lockForUpdate()->findOrFail($roomId);

            if (RoomDisplay::query()->where('room_id', $roomId)->exists()) {
                throw ValidationException::withMessages(['room_id' => 'Ruangan ini sudah memiliki perangkat display.']);
            }

            return RoomDisplay::query()->create([
                'room_id' => $roomId,
                'display_name' => $name,
                'display_token' => $this->uniqueToken(),
            ]);
        });
    }

    public function regenerateToken(RoomDisplay $display): RoomDisplay
    {
        return DB::transaction(function () use ($display): RoomDisplay {
            $lockedDisplay = RoomDisplay::query()->lockForUpdate()->findOrFail($display->id);
            $lockedDisplay->update(['display_token' => $this->uniqueToken()]);

            return $lockedDisplay->refresh();
        });
    }

    public function delete(RoomDisplay $display): void
    {
        DB::transaction(fn () => RoomDisplay::query()->lockForUpdate()->findOrFail($display->id)->delete());
    }

    private function uniqueToken(): string
    {
        do {
            $token = 'DISP-'.Str::upper(Str::random(40));
        } while (RoomDisplay::query()->where('display_token', $token)->exists());

        return $token;
    }
}
