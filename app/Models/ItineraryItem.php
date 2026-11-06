<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'itinerary_id',
        'day_number',
        'date',
        'time',
        'title',
        'activity',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'date' => 'date',
        ];
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }
}
