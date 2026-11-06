<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamaahContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'jamaah_id',
        'name',
        'relationship',
        'phone',
        'address',
        'is_emergency_contact',
    ];

    protected function casts(): array
    {
        return [
            'is_emergency_contact' => 'boolean',
        ];
    }

    public function jamaah(): BelongsTo
    {
        return $this->belongsTo(Jamaah::class);
    }
}
