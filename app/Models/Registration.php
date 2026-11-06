<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'jamaah_id',
        'package_id',
        'departure_id',
        'registration_date',
        'room_type',
        'agreed_price',
        'discount',
        'final_price',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'special_notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'agreed_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function getRoomTypeLabelAttribute(): string
    {
        return match($this->room_type) {
            'quad' => 'Quad (Ber-4)',
            'triple' => 'Triple (Ber-3)',
            'double' => 'Double (Ber-2)',
            'single' => 'Single (Ber-1)',
            default => ucfirst($this->room_type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'terdaftar' => 'Terdaftar',
            'proses' => 'Proses Kelengkapan',
            'siap_berangkat' => 'Siap Berangkat',
            'berangkat' => 'Berangkat',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
