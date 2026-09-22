<?php

namespace App\Services;

use App\Data\BookingData;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function create(User $user, BookingData $data): Booking
    {
        return DB::transaction(function () use ($user, $data): Booking {
            $room = Room::query()->lockForUpdate()->findOrFail($data->roomId);

            if ($room->status === 'maintenance') {
                throw ValidationException::withMessages(['room_id' => 'Ruangan sedang dalam perawatan dan tidak dapat dipesan.']);
            }

            if ($data->attendeesCount > $room->capacity) {
                throw ValidationException::withMessages(['attendees_count' => "Jumlah peserta melebihi kapasitas {$room->capacity} orang."]);
            }

            $hasConflict = $this->hasConflict($room->id, $data->date, $data->startTime, $data->endTime);
            $status = $user->isAdmin() && ! $hasConflict ? BookingStatus::Confirmed : BookingStatus::Pending;

            $booking = Booking::query()->create([
                'user_id' => $user->id,
                'user_name' => $data->organizerName,
                'user_dept' => $data->department,
                'room_id' => $room->id,
                'title' => $data->title,
                'date' => $data->date,
                'start_time' => $data->startTime,
                'end_time' => $data->endTime,
                'purpose' => $data->purpose,
                'activity_type' => $data->activityType->value,
                'attendees_count' => $data->attendeesCount,
                'status' => $status,
            ]);

            if ($status === BookingStatus::Pending) {
                $this->notifications->notifyAdminsOfPendingBooking($booking);
            }

            return $booking->load(['room', 'user']);
        }, attempts: 3);
    }

    public function approve(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            Room::query()->lockForUpdate()->findOrFail($lockedBooking->room_id);

            if ($lockedBooking->status !== BookingStatus::Pending) {
                throw ValidationException::withMessages(['booking' => 'Hanya pengajuan pending yang dapat disetujui.']);
            }

            if ($this->hasConflict(
                $lockedBooking->room_id,
                $lockedBooking->date->format('Y-m-d'),
                $lockedBooking->start_time,
                $lockedBooking->end_time,
                $lockedBooking->id,
                [BookingStatus::Confirmed],
            )) {
                throw ValidationException::withMessages(['booking' => 'Pengajuan berbenturan dengan jadwal yang sudah dikonfirmasi.']);
            }

            $lockedBooking->update([
                'status' => BookingStatus::Confirmed,
            ]);

            return $lockedBooking->refresh();
        }, attempts: 3);
    }

    public function reject(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if ($lockedBooking->status !== BookingStatus::Pending) {
                throw ValidationException::withMessages(['booking' => 'Hanya pengajuan pending yang dapat ditolak.']);
            }

            $lockedBooking->update(['status' => BookingStatus::Cancelled]);

            return $lockedBooking->refresh();
        }, attempts: 3);
    }

    public function cancel(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($lockedBooking->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)) {
                throw ValidationException::withMessages(['booking' => 'Booking ini tidak dapat dibatalkan.']);
            }

            $lockedBooking->update(['status' => BookingStatus::Cancelled]);

            return $lockedBooking->refresh();
        }, attempts: 3);
    }

    /** @param list<BookingStatus> $statuses */
    public function hasConflict(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        int $excludeId = 0,
        array $statuses = [BookingStatus::Pending, BookingStatus::Confirmed],
    ): bool {
        return Booking::query()
            ->where('room_id', $roomId)
            ->whereDate('date', $date)
            ->when($excludeId > 0, fn ($query) => $query->whereKeyNot($excludeId))
            ->whereIn('status', array_map(fn (BookingStatus $status) => $status->value, $statuses))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }
}
