<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MeetingNotification;
use App\Models\User;

class NotificationService
{
    public function notifyAdminsOfPendingBooking(Booking $booking): void
    {
        User::query()->where('role', 'admin')->each(function (User $admin) use ($booking): void {
            MeetingNotification::query()->updateOrCreate(
                [
                    'recipient_user_id' => $admin->id,
                    'booking_id' => $booking->id,
                    'type' => 'booking_pending',
                ],
                [
                    'title' => 'Pengajuan peminjaman baru',
                    'message' => sprintf('%s mengajukan agenda "%s".', $booking->user_name, $booking->title),
                    'is_read' => false,
                    'read_at' => null,
                ],
            );
        });
    }
}
