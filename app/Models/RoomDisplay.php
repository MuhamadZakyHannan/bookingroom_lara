<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomDisplay extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'display_name',
        'display_token',
        'last_active',
    ];

    protected $hidden = ['display_token'];

    protected function casts(): array
    {
        return ['last_active' => 'datetime'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
