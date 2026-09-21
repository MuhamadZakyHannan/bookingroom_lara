<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class RoomAdministrationService
{
    public function create(array $data, ?UploadedFile $image): Room
    {
        $imagePath = $image ? $this->storeImage($image) : 'public/rooms/KalTim.jpeg';

        try {
            return DB::transaction(fn (): Room => Room::query()->create($data + ['image' => $imagePath]));
        } catch (Throwable $exception) {
            $this->deleteManagedImage($imagePath);
            throw $exception;
        }
    }

    public function update(Room $room, array $data, ?UploadedFile $image): Room
    {
        $newImage = $image ? $this->storeImage($image) : null;
        $previousImage = null;

        try {
            $updatedRoom = DB::transaction(function () use ($room, $data, $newImage, &$previousImage): Room {
                $lockedRoom = Room::query()->lockForUpdate()->findOrFail($room->id);
                $previousImage = $lockedRoom->image;
                $lockedRoom->update($newImage ? $data + ['image' => $newImage] : $data);

                return $lockedRoom->refresh();
            });
        } catch (Throwable $exception) {
            $this->deleteManagedImage($newImage);
            throw $exception;
        }

        if ($newImage) {
            $this->deleteManagedImage($previousImage);
        }

        return $updatedRoom;
    }

    public function delete(Room $room): void
    {
        $image = DB::transaction(function () use ($room): ?string {
            $lockedRoom = Room::query()->lockForUpdate()->findOrFail($room->id);

            if ($lockedRoom->bookings()->exists() || $lockedRoom->displays()->exists()) {
                throw ValidationException::withMessages([
                    'room' => 'Ruangan memiliki riwayat booking atau perangkat display. Ubah status menjadi Perawatan agar data tetap aman.',
                ]);
            }

            $image = $lockedRoom->image;
            $lockedRoom->delete();

            return $image;
        });

        $this->deleteManagedImage($image);
    }

    private function storeImage(UploadedFile $image): string
    {
        $directory = public_path('rooms');
        File::ensureDirectoryExists($directory, 0755, true);
        $filename = 'room_'.Str::uuid().'.'.$image->guessExtension();
        $image->move($directory, $filename);

        return 'public/rooms/'.$filename;
    }

    private function deleteManagedImage(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'public/rooms/room_')) {
            return;
        }

        File::delete(public_path('rooms/'.basename($path)));
    }
}
