<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'package_id',
        'departure_date',
        'return_date',
        'origin_airport',
        'destination_airport',
        'airline',
        'flight_number',
        'departure_time',
        'arrival_time',
        'quota',
        'leader_name',
        'leader_phone',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'quota' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(DepartureChecklist::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getRegisteredJamaahsCountAttribute(): int
    {
        return $this->registrations()->where('status', '!=', 'batal')->count();
    }

    public function getRemainingSeatsAttribute(): int
    {
        return max(0, $this->quota - $this->registered_jamaahs_count);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'persiapan' => 'Persiapan',
            'siap_berangkat' => 'Siap Berangkat',
            'berangkat' => 'Berangkat',
            'dalam_perjalanan' => 'Dalam Perjalanan',
            'pulang' => 'Kepulangan',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
