<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_number',
        'category_id',
        'departure_id',
        'expense_type',
        'title',
        'expense_date',
        'amount',
        'payment_method',
        'proof_file',
        'notes',
        'recorded_by',
        'is_cancelled',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'is_cancelled' => 'boolean',
        ];
    }

    public function isDirect(): bool
    {
        return $this->expense_type === 'langsung' || (!empty($this->departure_id) && empty($this->expense_type));
    }

    public function isGeneral(): bool
    {
        return $this->expense_type === 'operasional_umum' || (empty($this->departure_id) && empty($this->expense_type));
    }

    public function getExpenseTypeLabelAttribute(): string
    {
        return $this->isDirect() ? 'Langsung (Kloter)' : 'Operasional Umum';
    }

    public function scopeActive($query)
    {
        return $query->where('is_cancelled', false);
    }

    public function scopeDirect($query)
    {
        return $query->where(function ($q) {
            $q->where('expense_type', 'langsung')
              ->orWhere(function ($sq) {
                  $sq->whereNull('expense_type')->whereNotNull('departure_id');
              });
        });
    }

    public function scopeGeneral($query)
    {
        return $query->where(function ($q) {
            $q->where('expense_type', 'operasional_umum')
              ->orWhere(function ($sq) {
                  $sq->whereNull('expense_type')->whereNull('departure_id');
              });
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
