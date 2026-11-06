<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'registration_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function validPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', 'valid');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Recalculate invoice paid_amount, remaining_amount, and update status.
     * Formula: Sisa tagihan = Total tagihan - Total pembayaran valid.
     */
    public function recalculateFinancials(): void
    {
        $validPaid = (float) $this->validPayments()->sum('amount');
        $total = (float) $this->total_amount;
        $remaining = max(0, $total - $validPaid);

        $this->paid_amount = $validPaid;
        $this->remaining_amount = $remaining;

        if ($validPaid >= $total && $total > 0) {
            $this->status = 'lunas';
        } elseif ($validPaid > 0) {
            $this->status = 'sebagian';
        } else {
            $this->status = 'belum_bayar';
        }

        $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'belum_bayar' => 'Belum Bayar',
            'sebagian' => 'Sebagian / Cicilan',
            'lunas' => 'Lunas',
            'batal' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
