<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        private readonly AttendancePolicyService $policy,
        private readonly NotificationService $notifications,
    ) {}

    public function checkIn(Booking $booking, User $user): Booking
    {
        return DB::transaction(function () use ($booking, $user): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            abort_unless($lockedBooking->user_id === $user->id, 403);

            if (! $this->policy->state($lockedBooking)->canCheckIn) {
                throw ValidationException::withMessages(['attendance' => 'Check-in belum tersedia atau batas waktunya telah lewat.']);
            }

            $lockedBooking->update([
                'attendance_status' => AttendanceStatus::CheckedIn,
                'check_in_at' => now(),
                'attendance_updated_by' => 'user',
            ]);
            $this->notifications->notifyAdminsOfAttendance(
                $lockedBooking,
                'attendance_checked_in',
                "{$lockedBooking->user_name} check-in untuk agenda \"{$lockedBooking->title}\".",
            );

            return $lockedBooking->refresh();
        }, attempts: 3);
    }

    public function checkOut(Booking $booking, User $user): Booking
    {
        return DB::transaction(function () use ($booking, $user): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            abort_unless($lockedBooking->user_id === $user->id, 403);

            if (! $this->policy->state($lockedBooking)->canCheckOut) {
                throw ValidationException::withMessages(['attendance' => 'Check-out tidak tersedia untuk booking ini.']);
            }

            $lockedBooking->update([
                'status' => BookingStatus::Completed,
                'attendance_status' => AttendanceStatus::CheckedOut,
                'check_out_at' => now(),
                'attendance_updated_by' => 'user',
            ]);
            $this->notifications->notifyAdminsOfAttendance(
                $lockedBooking,
                'attendance_checked_out',
                "{$lockedBooking->user_name} check-out dari agenda \"{$lockedBooking->title}\".",
            );

            return $lockedBooking->refresh();
        }, attempts: 3);
    }

    /** @return array{no_show: int, checked_out: int} */
    public function processAutomaticTransitions(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $result = ['no_show' => 0, 'checked_out' => 0];

        Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereDate('date', '<=', $now->toDateString())
            ->whereIn('attendance_status', [AttendanceStatus::Scheduled->value, AttendanceStatus::CheckedIn->value])
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($now, &$result): void {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, $now, &$result): void {
                        $locked = Booking::query()->lockForUpdate()->find($booking->id);
                        if (! $locked || $locked->status !== BookingStatus::Confirmed) {
                            return;
                        }

                        if ($locked->attendance_status === AttendanceStatus::Scheduled
                            && $now->greaterThan($locked->startsAt()->addMinutes($this->policy->graceMinutes()))) {
                            $locked->update([
                                'status' => BookingStatus::Completed,
                                'attendance_status' => AttendanceStatus::NoShow,
                                'no_show_at' => $now,
                                'attendance_updated_by' => 'system',
                            ]);
                            $result['no_show']++;
                            $this->notifications->notifyAdminsOfAttendance($locked, 'attendance_no_show', "Agenda \"{$locked->title}\" ditandai tidak hadir.");
                        } elseif ($locked->attendance_status === AttendanceStatus::CheckedIn
                            && $now->greaterThanOrEqualTo($locked->endsAt())) {
                            $locked->update([
                                'status' => BookingStatus::Completed,
                                'attendance_status' => AttendanceStatus::CheckedOut,
                                'check_out_at' => $now,
                                'attendance_updated_by' => 'system',
                            ]);
                            $result['checked_out']++;
                            $this->notifications->notifyAdminsOfAttendance($locked, 'attendance_auto_checked_out', "Agenda \"{$locked->title}\" otomatis check-out.");
                        }
                    }, attempts: 3);
                }
            });

        return $result;
    }
}
