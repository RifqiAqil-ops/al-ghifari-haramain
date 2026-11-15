<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'unit',
        'stock',
        'min_stock',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(EquipmentMutation::class, 'equipment_id')->latest();
    }

    public function distributionItems(): HasMany
    {
        return $this->hasMany(EquipmentDistributionItem::class, 'equipment_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function getStockInAttribute(): int
    {
        return (int) $this->mutations()->where('type', 'in')->sum('quantity');
    }

    public function getStockOutAttribute(): int
    {
        return (int) $this->mutations()->where('type', 'out')->sum('quantity');
    }

    public function getStockDamagedAttribute(): int
    {
        return (int) $this->mutations()->where('type', 'damaged')->sum('quantity');
    }
}
