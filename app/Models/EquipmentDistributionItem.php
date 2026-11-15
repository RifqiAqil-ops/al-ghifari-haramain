<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDistributionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'equipment_id',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(EquipmentDistribution::class, 'distribution_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id')->withTrashed();
    }
}
