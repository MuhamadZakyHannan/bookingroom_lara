<?php

namespace App\Data;

use App\Enums\ActivityType;

final readonly class BookingData
{
    public function __construct(
        public int $roomId,
        public string $organizerName,
        public string $department,
        public string $title,
        public string $date,
        public string $startTime,
        public string $endTime,
        public ?string $purpose,
        public ActivityType $activityType,
        public int $attendeesCount,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            roomId: (int) $data['room_id'],
            organizerName: trim($data['user_name']),
            department: trim($data['user_dept']),
            title: trim($data['title']),
            date: $data['date'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            purpose: isset($data['purpose']) ? trim($data['purpose']) : null,
            activityType: ActivityType::from($data['activity_type']),
            attendeesCount: (int) $data['attendees_count'],
        );
    }
}
