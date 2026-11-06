<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'refund_number',
        'registration_id',
        'invoice_id',
        'refund_date',
        'amount',
        'reason',
        'payment_method',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'approved_by',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'refund_date' => 'date',
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
