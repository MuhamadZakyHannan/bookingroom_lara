<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class ProcessAttendance extends Command
{
    protected $signature = 'attendance:process';

    protected $description = 'Memproses no-show dan check-out otomatis untuk booking yang jatuh tempo';

    public function handle(AttendanceService $attendance): int
    {
        $result = $attendance->processAutomaticTransitions();
        $this->info("No-show: {$result['no_show']}; auto check-out: {$result['checked_out']}.");

        return self::SUCCESS;
    }
}
