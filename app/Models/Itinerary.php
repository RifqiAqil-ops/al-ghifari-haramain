<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_id',
        'title',
        'description',
    ];

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItineraryItem::class)->orderBy('day_number');
    }
}
