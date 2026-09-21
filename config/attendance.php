<?php

return [
    'check_in_early_minutes' => (int) env('ATTENDANCE_CHECK_IN_EARLY_MINUTES', 15),
    'grace_minutes' => (int) env('ATTENDANCE_GRACE_MINUTES', 15),
];
