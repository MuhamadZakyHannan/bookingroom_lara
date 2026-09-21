<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Scheduled = 'scheduled';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Belum Check-in',
            self::CheckedIn => 'Sudah Check-in',
            self::CheckedOut => 'Sudah Check-out',
            self::NoShow => 'Tidak Hadir',
        };
    }
}
