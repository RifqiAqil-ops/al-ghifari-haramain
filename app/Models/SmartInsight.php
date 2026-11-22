<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'level',
        'title',
        'summary',
        'details',
        'action_text',
        'action_url',
        'source_entity_type',
        'source_entity_id',
        'status',
        'resolved_by',
        'resolution_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCriticalOrWarning($query)
    {
        return $query->whereIn('level', ['critical', 'warning']);
    }
}
