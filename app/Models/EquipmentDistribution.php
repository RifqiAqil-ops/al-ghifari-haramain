<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_number',
        'jamaah_id',
        'departure_id',
        'distribution_date',
        'status',
        'received_by',
        'notes',
        'distributed_by',
    ];

    protected function casts(): array
    {
        return [
            'distribution_date' => 'date',
        ];
    }

    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class, 'jamaah_id')->withTrashed();
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class, 'departure_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EquipmentDistributionItem::class, 'distribution_id');
    }

    public static function generateDistributionNumber(): string
    {
        $prefix = 'DST-' . date('Ym') . '-';
        $last = self::where('distribution_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->distribution_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
