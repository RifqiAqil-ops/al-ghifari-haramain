<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentOcrLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'jamaah_id',
        'document_id',
        'document_type',
        'extracted_data',
        'confidence_score',
        'validation_status',
        'validation_notes',
        'verification_status',
        'verified_by',
        'verification_note',
        'verified_at',
        'applied_to_jamaah',
    ];

    protected function casts(): array
    {
        return [
            'extracted_data' => 'array',
            'confidence_score' => 'decimal:2',
            'verified_at' => 'datetime',
            'applied_to_jamaah' => 'boolean',
        ];
    }

    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(JamaahDocument::class, 'document_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
