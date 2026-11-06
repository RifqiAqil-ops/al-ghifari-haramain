<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'program_category',
        'haji_year',
        'price',
        'quad_price',
        'triple_price',
        'double_price',
        'duration_days',
        'quota',
        'airline',
        'hotel_makkah',
        'hotel_makkah_rating',
        'hotel_madinah',
        'hotel_madinah_rating',
        'start_period',
        'end_period',
        'description',
        'image',
        'is_promo',
        'discount_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quad_price' => 'decimal:2',
            'triple_price' => 'decimal:2',
            'double_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'duration_days' => 'integer',
            'quota' => 'integer',
            'hotel_makkah_rating' => 'integer',
            'hotel_madinah_rating' => 'integer',
            'is_promo' => 'boolean',
            'start_period' => 'date',
            'end_period' => 'date',
        ];
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(PackageFacility::class);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->is_promo ? max(0, $this->price - $this->discount_amount) : $this->price);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'haji' ? 'Haji' : 'Umrah';
    }
}
