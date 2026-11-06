<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_id',
        'flight_type',
        'airline',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_time',
        'arrival_time',
        'terminal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
        ];
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }
}
