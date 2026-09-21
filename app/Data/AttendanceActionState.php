<?php

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class AttendanceActionState
{
    public function __construct(
        public bool $canCheckIn,
        public bool $canCheckOut,
        public string $message,
        public CarbonImmutable $checkInOpensAt,
        public CarbonImmutable $checkInDeadlineAt,
    ) {}
}
