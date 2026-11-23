<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(HotelCustomer::class, 'customer_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(HotelContract::class, 'contract_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HotelReservationItem::class, 'reservation_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(HotelInvoice::class, 'reservation_id');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(HotelCost::class, 'reservation_id');
    }
}
