<?php

namespace App\Services;

use App\Data\AttendanceActionState;
use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\CarbonImmutable;

class AttendancePolicyService
{
    public function state(Booking $booking, ?CarbonImmutable $now = null): AttendanceActionState
    {
        $now ??= CarbonImmutable::now();
        $opensAt = $booking->startsAt()->subMinutes($this->earlyMinutes());
        $deadline = $booking->startsAt()->addMinutes($this->graceMinutes());

        if ($booking->status !== BookingStatus::Confirmed || $booking->attendance_status === null) {
            return new AttendanceActionState(false, false, '', $opensAt, $deadline);
        }

        if ($booking->attendance_status === AttendanceStatus::CheckedIn) {
            $canCheckOut = $now->lessThan($booking->endsAt());

            return new AttendanceActionState(
                false,
                $canCheckOut,
                $canCheckOut ? 'Rapat sedang berlangsung. Check-out tersedia.' : 'Menunggu auto check-out.',
                $opensAt,
                $deadline,
            );
        }

        if ($booking->attendance_status !== AttendanceStatus::Scheduled) {
            return new AttendanceActionState(false, false, '', $opensAt, $deadline);
        }

        if ($now->lessThan($opensAt)) {
            $message = "Check-in dibuka {$this->earlyMinutes()} menit sebelum jadwal.";
            $canCheckIn = false;
        } elseif ($now->lessThanOrEqualTo($deadline)) {
            $message = 'Check-in tersedia sampai '.$deadline->format('H:i').' WIB.';
            $canCheckIn = true;
        } else {
            $message = 'Batas check-in telah lewat.';
            $canCheckIn = false;
        }

        return new AttendanceActionState($canCheckIn, false, $message, $opensAt, $deadline);
    }

    public function earlyMinutes(): int
    {
        return max(0, (int) config('attendance.check_in_early_minutes', 15));
    }

    public function graceMinutes(): int
    {
        return max(0, (int) config('attendance.grace_minutes', 15));
    }
}
