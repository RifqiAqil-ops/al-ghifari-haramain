<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'trigger_source',
        'action_taken',
        'status',
        'target_type',
        'target_id',
        'idempotency_key',
        'result_data',
        'error_message',
        'executed_by',
    ];

    protected function casts(): array
    {
        return [
            'result_data' => 'array',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'rule_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
