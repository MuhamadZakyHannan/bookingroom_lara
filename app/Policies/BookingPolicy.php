<?php

namespace App\Policies;

use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id
            && in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)
            && $booking->attendance_status !== AttendanceStatus::CheckedIn;
    }

    public function checkIn(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    public function checkOut(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }
}
