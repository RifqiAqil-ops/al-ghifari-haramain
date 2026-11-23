<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelExchangeRate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'fetched_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_manual_override' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
