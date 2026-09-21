<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'capacity',
        'location',
        'floor',
        'status',
        'image',
        'description',
        'facilities',
    ];

    protected function casts(): array
    {
        return ['capacity' => 'integer'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function displays(): HasMany
    {
        return $this->hasMany(RoomDisplay::class);
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->image ?: 'public/rooms/KalTim.jpeg';

        return asset(preg_replace('#^public/#', '', $path));
    }
}
