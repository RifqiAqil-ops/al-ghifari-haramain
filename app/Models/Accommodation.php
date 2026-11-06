<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_id',
        'city',
        'hotel_name',
        'check_in_date',
        'check_out_date',
        'address',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
        ];
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }
}
