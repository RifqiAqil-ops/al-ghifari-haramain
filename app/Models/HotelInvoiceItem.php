<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelInvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(HotelInvoice::class, 'invoice_id');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
