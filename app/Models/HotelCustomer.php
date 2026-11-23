<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function reservations(): HasMany
    {
        return $this->hasMany(HotelReservation::class, 'customer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(HotelInvoice::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HotelPayment::class, 'customer_id');
    }
}
