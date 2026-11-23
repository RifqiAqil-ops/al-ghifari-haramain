<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function roomTypes(): HasMany
    {
        return $this->hasMany(HotelRoomType::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(HotelContract::class);
    }

    public function reservationItems(): HasMany
    {
        return $this->hasMany(HotelReservationItem::class);
    }
}
