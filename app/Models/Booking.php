<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_dept',
        'room_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'purpose',
        'activity_type',
        'attendees_count',
        'status',
        'attendance_status',
        'check_in_at',
        'check_out_at',
        'no_show_at',
        'attendance_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'attendees_count' => 'integer',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'no_show_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(MeetingNotification::class);
    }
}
