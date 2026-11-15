<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'damaged' => 'Stok Rusak',
            'adjustment' => 'Penyesuaian',
            default => ucfirst($this->type),
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            'in' => 'badge-subtle-success',
            'out' => 'badge-subtle-info',
            'damaged' => 'badge-subtle-danger',
            'adjustment' => 'badge-subtle-warning',
            default => 'bg-secondary',
        };
    }
}
