<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamaahDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'jamaah_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'file_type',
        'status',
        'verification_note',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'ktp' => 'KTP Elektronik',
            'kk' => 'Kartu Keluarga',
            'paspor' => 'Paspor',
            'pas_foto' => 'Pas Foto 4x6',
            'buku_nikah' => 'Buku Nikah / Akta Lahir',
            'dokumen_pendukung' => 'Dokumen Pendukung',
            default => ucfirst(str_replace('_', ' ', $this->document_type)),
        };
    }
}
