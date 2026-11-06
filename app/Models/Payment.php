<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'transaction_number',
        'receipt_number',
        'payment_date',
        'amount',
        'payment_type',
        'payment_method',
        'bank_name',
        'sender_name',
        'proof_file',
        'status',
        'notes',
        'received_by',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        return match($this->payment_type) {
            'dp' => 'Uang Muka (DP)',
            'cicilan' => 'Cicilan',
            'pelunasan' => 'Pelunasan',
            default => ucfirst($this->payment_type),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'transfer' => 'Transfer Bank',
            'tunai' => 'Tunai / Cash',
            default => ucfirst($this->payment_method),
        };
    }
}
